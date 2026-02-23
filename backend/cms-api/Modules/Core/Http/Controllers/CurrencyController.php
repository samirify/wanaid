<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\Currency;
use Modules\Core\Services\CurrencyService;
use SoulDoit\DataTable\SSP;

class CurrencyController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    /**
     * constructor
     */
    public function __construct(
        private readonly CurrencyService $currencyService
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SSP $ssp, Request $request)
    {
        $ssp->enableSearch();
        $ssp->setColumns([
            ['label' => 'ID',           'db' => 'id'],
            ['label' => 'Name',         'db' => 'name'],
            ['label' => 'Code',         'db' => 'code'],
            ['label' => 'Symbol',       'db' => 'symbol'],
            ['label' => 'Active',       'db' => 'active'],
            ['label' => 'Default',      'db' => 'default'],
            ['label' => 'Last updated', 'db' => 'updated_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('currencies')
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->successResponse();
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
            'name'               => 'required',
            'code'               => 'required',
        ], [
            'name.required' => 'Name is required',
            'code.required' => 'Code is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $currency = new Currency();

            $currency->name = $request->name;
            $currency->code = $request->code;
            $currency->symbol = $request->symbol;
            $currency->default = determineBool($request->default);
            $currency->active = determineBool($request->active);

            if ($currency->default) {
                Currency::query()->update(['default' => false]);
                $currency->active = true;
            }

            $currency->save();

            $currenciesCount = Currency::where(['default' => true])->count();

            if (!$currenciesCount) {
                throw new Exception("You must have one default currency! Please set this currency as default.", 400);
            }

            DB::commit();

            return $this->successResponse([
                'msg' => 'Created successfully!',
            ]);
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
        return $this->successResponse([
            'currency' => Currency::find($id),
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
        $currency = Currency::find($id);

        $originalDefault = determineBool($currency->default);
        $newDefault = determineBool($request->default);

        if ($originalDefault && !$newDefault) {
            return $this->errorResponse('You must have one default currency. Please set another currency as default and try again', 400);
        }

        $validator = Validator::make($request->all(), [
            'name'               => 'required',
            'code'               => 'required',
        ], [
            'name.required' => 'Name is required',
            'code.required' => 'Code is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $currency->name = $request->name;
            $currency->code = $request->code;
            $currency->symbol = $request->symbol;

            $currency->default = determineBool($request->default);
            $currency->active = determineBool($request->active);

            if ($currency->default) {
                Currency::query()->update(['default' => false]);
                $currency->active = true;
            }

            $currency->save();

            DB::commit();

            return $this->successResponse([
                'msg' => 'Updated successfully!',
                '$currency' => $currency
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
            $currency = Currency::find($id);
            $currencyName = $currency->name;
            $currency->delete();

            $currenciesCount = Currency::where(['default' => true])->count();

            if (!$currenciesCount) {
                throw new Exception("You must have one default currency! Please set another currency as default and try again.", 400);
            }

            DB::commit();
            return $this->successResponse([
                'msg' => $currencyName . ' currency was successfully deleted'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
