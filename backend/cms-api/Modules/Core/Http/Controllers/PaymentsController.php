<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\Payment;
use Modules\Core\Services\PaymentService;
use Modules\Core\Services\StatsService;
use SoulDoit\DataTable\SSP;

class PaymentsController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    /**
     * constructor
     */
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly StatsService $statsService,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SSP $ssp, Request $request)
    {
        $ssp->enableSearch();
        $ssp->setColumns([
            ['label' => 'ID',                   'db' => 'id'],
            ['label' => 'Code',                 'db' => 'code'],
            ['label' => 'Amount',               'db' => 'amount'],
            ['label' => 'Payment Method',       'db' => 'payment_method'],
            ['label' => 'Payment Status',       'db' => 'payment_status'],
            ['label' => 'Payment Status Code',  'db' => 'payment_status_code'],
            ['label' => 'Updated By',           'db' => 'updated_by'],
            ['label' => 'Created At',           'db' => 'created_at'],
            ['label' => 'Last Modified At',     'db' => 'last_modified_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_payments')
                ->select($selected_columns);

            $query = $this->filterDataTable($request, $query);

            return $query;
        });

        $result = $ssp->getData();

        return $this->successResponse([
            'records' => $result['items'],
            'totalRecords' => $result['total_item_count'],
        ]);
    }

    /**
     * Get Paypal order details.
     *
     * @return \Illuminate\Http\Response
     */
    public function payPalOrderDetails($orderId)
    {
        $paymentRecord = DB::table('payments AS p')
            ->select('c.id AS id', 'lt.text AS title')
            ->leftJoin('language_code AS lc', 'lc.code', '=', 'c.title')
            ->leftJoin('language_translation AS lt', 'lt.language_code_id', '=', 'lc.id')
            ->leftJoin('language AS l', 'lt.language_id', '=', 'l.id')
            ->where([
                'p.code' => $orderId
            ])->first();

        return $this->successResponse([
            'payment' => $paymentRecord,
            'orderId' => $orderId,
            'orderDetails' => $this->paymentService->getPayPalOrderDetails($orderId)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->successResponse([
            'payment_methods' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAYMENT_METHOD',
                ])->get()->toArray(),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAYMENT_STATUS',
                ])->get()->toArray()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'              => 'required|max:32',
            'amount'            => 'required',
            'payment_method_id' => 'required',
            // 'entity_id'         => 'required',
            'status_id'         => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $payment = new Payment();

            $payment->code = strtoupper($request->code);
            $payment->amount = $request->amount;
            // $payment->entity_name = '';
            $payment->entity_id = $request->entity_id;
            $payment->payment_method_id = $request->payment_method_id;
            $payment->status_id = $request->status_id;

            $payment->save();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Payment ' . $payment->code . ' created successfully!'
            ]);

            $this->statsService->updateStatsCategory('payments');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            $this->errorResponse('Payment not found!', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse([
            'payment' => $payment,
            'payment_methods' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAYMENT_METHOD',
                ])->get()->toArray(),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PAYMENT_STATUS',
                ])->get()->toArray()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the data
        $payment = Payment::find($id);
        $paymentCurrentStatusId = $payment->status_id;

        $validator = Validator::make($request->all(), [
            'status_id'         => 'required',
            'message'           => ''
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        try {
            $payment->status_id = $request->status_id;
            $payment->last_modified_at = now();
            $payment->updated_by = auth('api')->user()->id;

            $payment->save();

            updateStatusChange([
                'entity_name' => '',
                'entity_id' => (string)$payment->id,
                'message' => $data['message'] ?? 'Manually updated',
                'status_from_id' => $paymentCurrentStatusId,
                'status_to_id' => $payment->status_id,
            ]);

            DB::commit();

            $this->statsService->updateStatsCategory('payments');

            return $this->successResponse([
                'msg' => 'Updated successfully!'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::find($id);
            $paymentName = $payment->code;
            $payment->delete();

            DB::commit();

            $this->statsService->updateStatsCategory('payments');

            return $this->successResponse([
                'msg' => 'Payment ' . $paymentName . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function getDashboardStatsByMonths(int $months)
    {
        $result = $this->paymentService->getPaymentsStats(months: $months);

        return $this->successResponse([
            'statsData' => $result,
        ]);
    }
}
