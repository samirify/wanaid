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
use Modules\Client\Services\ClientIdentityThemeService;
use Modules\Core\Models\MediaStore;
use Modules\Core\Traits\MediaTrait;
use SoulDoit\DataTable\SSP;

class ClientThemeController extends Controller
{
    use AppHelperTrait, SAAApiResponse, MediaTrait;

    public function __construct(
        private readonly ClientIdentityThemeService $clientIdentityThemeService
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
            ['label' => 'Code',               'db' => 'code'],
            ['label' => 'Name',               'db' => 'name'],
            ['label' => 'Default',            'db' => 'default'],
            ['label' => 'Active',             'db' => 'active'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('client_identity_themes')
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
        return $this->successResponse([
            'available_client_identities' => ClientIdentity::all(['name', 'id'])->toArray()
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
            'code'                  => 'required',
            'name'                  => 'required',
            'client_identity_id'    => 'required',
            'primary_colour'        => 'required',
            'secondary_colour'      => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $clientTheme = new ClientIdentityTheme();

            $clientTheme->code = $request->code;
            $clientTheme->name = $request->name;
            $clientTheme->client_identity_id = $request->client_identity_id;

            $clientTheme->primary_colour_id = $this->clientIdentityThemeService->getColourIdFromHex($request->primary_colour);
            $clientTheme->secondary_colour_id = $this->clientIdentityThemeService->getColourIdFromHex($request->secondary_colour);

            $clientTheme->default = determineBool($request->default);
            $clientTheme->active = determineBool($request->active);

            if ($clientTheme->default) {
                ClientIdentityTheme::query()->where('default', '=', 1)->update(['default' => 0]);
                $clientTheme->active = true;
            }

            $logoColouredLightImgFile = null;

            if (!is_null($request->logo_coloured_light_id)) {
                $logoColouredLightImgFile = $this->uploadFile($request, [
                    'field_name' => 'logo_coloured_light_id',
                    'file_name' => $this->formatUniqueTitle($clientTheme->name || $clientTheme->code),
                ], [
                    'name' => 'ClientLogoColouredLightImage',
                    'id' => '.'
                ], []);

                $clientTheme->logo_coloured_light_id = $logoColouredLightImgFile->id;
            }

            $logoColouredDarkImgFile = null;

            if (!is_null($request->logo_coloured_dark_id)) {
                $logoColouredDarkImgFile = $this->uploadFile($request, [
                    'field_name' => 'logo_coloured_dark_id',
                    'file_name' => $this->formatUniqueTitle($clientTheme->name || $clientTheme->code),
                ], [
                    'name' => 'ClientLogoColouredDarkImage',
                    'id' => '.'
                ], []);

                $clientTheme->logo_coloured_dark_id = $logoColouredDarkImgFile->id;
            }

            $clientTheme->save();

            $clientThemeCount = ClientIdentityTheme::where(['default' => true])->count();

            if (!$clientThemeCount) {
                throw new Exception("You must have one default theme! Please set another theme as default and try again.", 400);
            }

            if ($logoColouredLightImgFile) {
                $logoColouredLightImgFile->update([
                    'entity_id' => (string)$clientTheme->id
                ]);
            }

            if ($logoColouredDarkImgFile) {
                $logoColouredDarkImgFile->update([
                    'entity_id' => (string)$clientTheme->id
                ]);
            }

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
        $clientTheme = ClientIdentityTheme::with('primaryColour', 'secondaryColour')->find($id);

        $clientTheme->primary_colour = $clientTheme->primaryColour->hex;
        $clientTheme->secondary_colour = $clientTheme->secondaryColour->hex;

        unset($clientTheme->primaryColour, $clientTheme->secondaryColour);

        return $this->successResponse([
            'theme' => $clientTheme,
            'available_client_identities' => ClientIdentity::all(['name', 'id'])->toArray(),
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
        $clientTheme = ClientIdentityTheme::find($id);

        $validator = Validator::make($request->all(), [
            'code'                  => 'required',
            'name'                  => 'required',
            'client_identity_id'    => 'required',
            'primary_colour'        => 'required',
            'secondary_colour'      => 'required',
            'logo_coloured_light_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $clientTheme->code = $request->code;
            $clientTheme->name = $request->name;
            $clientTheme->client_identity_id = $request->client_identity_id;

            $clientTheme->primary_colour_id = $this->clientIdentityThemeService->getColourIdFromHex($request->primary_colour);
            $clientTheme->secondary_colour_id = $this->clientIdentityThemeService->getColourIdFromHex($request->secondary_colour);

            $clientTheme->default = determineBool($request->default);
            $clientTheme->active = determineBool($request->active);

            if ($clientTheme->default) {
                ClientIdentityTheme::query()->where('default', '=', 1)->update(['default' => 0]);
                $clientTheme->active = true;
            }

            $logoColouredLightImgFile = null;

            if (!is_numeric($request->logo_coloured_light_id)) {
                $logoColouredLightImgFile = $this->uploadFile($request, [
                    'field_name' => 'logo_coloured_light_id',
                    'file_name' => $this->formatUniqueTitle($clientTheme->name || $clientTheme->code),
                ], [
                    'name' => 'ClientLogoColouredLightImage',
                    'id' => '.'
                ], []);

                $clientTheme->logo_coloured_light_id = $logoColouredLightImgFile->id;
            }

            $logoColouredDarkImgFile = null;

            if ($request->logo_coloured_dark_id && !is_numeric($request->logo_coloured_dark_id)) {
                $logoColouredDarkImgFile = $this->uploadFile($request, [
                    'field_name' => 'logo_coloured_dark_id',
                    'file_name' => $this->formatUniqueTitle($clientTheme->name || $clientTheme->code),
                ], [
                    'name' => 'ClientLogoColouredDarkImage',
                    'id' => '.'
                ], []);

                $clientTheme->logo_coloured_dark_id = $logoColouredDarkImgFile->id;
            }

            $clientTheme->save();

            $clientThemeCount = ClientIdentityTheme::where(['default' => true])->count();

            if (!$clientThemeCount) {
                throw new Exception("You must have one default theme! Please set another theme as default and try again.", 400);
            }

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
            $clientTheme = ClientIdentityTheme::find($id);
            $clientThemeName = $clientTheme->name;
            $clientTheme->delete();

            MediaStore::where([
                'entity_name' => 'ClientLogoColouredLightImage',
                'entity_id' => (string)$id
            ])->delete();

            MediaStore::where([
                'entity_name' => 'ClientLogoDarkLightImage',
                'entity_id' => (string)$id
            ])->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Theme ' . $clientThemeName . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
