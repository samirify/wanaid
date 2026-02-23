<?php

namespace Modules\Client\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Client\Models\ClientIdentity;
use Modules\Client\Models\ClientIdentityTheme;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\TranslationService;
use SoulDoit\DataTable\SSP;

class ClientIdentityController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    public function __construct(
        private readonly TranslationService $translationService
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
            ['label' => 'ID',                 'db' => 'id'],
            ['label' => 'Name',               'db' => 'name'],
            ['label' => 'Business Name',      'db' => 'business_name'],
            ['label' => 'Business Slogan',    'db' => 'business_slogan'],
            ['label' => 'Default',            'db' => 'default'],
            ['label' => 'Active',             'db' => 'active'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_client_identities')
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
            'name'              => 'required',
            'business_name'     => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $clientIdentity = new ClientIdentity();

            $clientIdentity->name = $request->name;
            $clientIdentity->business_name = $request->business_name;
            $clientIdentity->business_slogan = $request->business_slogan;
            $clientIdentity->business_short_description = $request->business_short_description;
            $clientIdentity->default = determineBool($request->default);
            $clientIdentity->active = determineBool($request->active);

            if ($clientIdentity->default) {
                ClientIdentity::query()->where('default', '=', 1)->update(['default' => 0]);
                $clientIdentity->active = true;
            }

            $clientIdentity->save();

            $clientIdentitiesCount = ClientIdentity::where(['default' => true])->count();

            if (!$clientIdentitiesCount) {
                throw new Exception("You must have one default identity! Please set another identity as default and try again.", 400);
            }

            $prefix = 'CLIENT_IDENTITY_' . $clientIdentity->id . '_';
            $clientIdentity->update([
                'business_name' => str_replace('TMPSET_', $prefix, $clientIdentity->business_name),
                'business_slogan' => str_replace('TMPSET_', $prefix, $clientIdentity->business_slogan),
                'business_short_description' => str_replace('TMPSET_', $prefix, $clientIdentity->business_short_description),
            ]);

            $this->translationService->translateFields([
                'business_name',
                'business_slogan',
                'business_short_description',
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Created successfully!'
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
        $clientIdentity = ClientIdentity::find($id);

        return $this->successResponse([
            'identity' => $clientIdentity,
            'translations' => getCodesTranslations([
                'CLIENT_IDENTITY_' . $clientIdentity->id . '_business_name',
                'CLIENT_IDENTITY_' . $clientIdentity->id . '_business_slogan',
                'CLIENT_IDENTITY_' . $clientIdentity->id . '_business_short_description',
            ])
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
        $clientIdentity = ClientIdentity::find($id);

        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'business_name'     => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $clientIdentity->name = $request->name;
            $clientIdentity->business_name = $request->business_name;
            $clientIdentity->business_slogan = $request->business_slogan;
            $clientIdentity->business_short_description = $request->business_short_description;

            $clientIdentity->default = determineBool($request->default);
            $clientIdentity->active = determineBool($request->active);

            if ($clientIdentity->default) {
                ClientIdentity::query()->where('default', '=', 1)->update(['default' => 0]);
                $clientIdentity->active = true;
            }

            $clientIdentity->save();

            $clientIdentitiesCount = ClientIdentity::where(['default' => true])->count();

            if (!$clientIdentitiesCount) {
                throw new Exception("You must have one default identity! Please set another identity as default and try again.", 400);
            }

            $prefix = 'CLIENT_IDENTITY_' . $clientIdentity->id . '_';
            $this->translationService->translateFields([
                'business_name',
                'business_slogan',
                'business_short_description',
            ], $request->all(), $prefix);

            DB::commit();
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
            // Delete related themes
            $relatedThemes = ClientIdentityTheme::where('client_identity_id', $id)->get();

            foreach ($relatedThemes as $theme) {
                $theme->delete();

                MediaStore::where([
                    'entity_name' => 'ClientLogoColouredLightImage',
                    'entity_id' => (string)$theme->id
                ])->delete();

                MediaStore::where([
                    'entity_name' => 'ClientLogoDarkLightImage',
                    'entity_id' => (string)$theme->id
                ])->delete();
            }

            $clientIdentity = ClientIdentity::find($id);
            $clientIdentityName = getLanguageTranslation($clientIdentity->name);

            // Clean translations
            LanguageCode::whereIn('code', [
                $clientIdentity->name,
                $clientIdentity->sub_header,
            ])->delete();

            $clientIdentity->delete();

            $clientIdentitiesCount = ClientIdentity::where(['default' => true])->count();

            if (!$clientIdentitiesCount) {
                throw new Exception("You must have one default identity! Please set another identity as default and try again.", 400);
            }

            DB::commit();
            return $this->successResponse([
                'msg' => 'Identity ' . $clientIdentityName . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
