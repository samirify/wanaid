<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\Language;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Services\LanguageService;
use Modules\Core\Services\SettingsService;
use Modules\Core\Services\TranslationService;
use SoulDoit\DataTable\SSP;

class LanguageController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    /**
     * constructor
     */
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly TranslationService $translationService,
        private readonly SettingsService $settingsService,
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
            ['label' => 'ID',         'db' => 'id'],
            ['label' => 'Locale',     'db' => 'locale_name'],
            ['label' => 'Name',       'db' => 'name'],
            ['label' => 'Default',    'db' => 'default'],
            ['label' => 'Active',     'db' => 'active'],
            ['label' => 'Available',  'db' => 'available'],
            ['label' => 'Created At', 'db' => 'created_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_languages')
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
            'countries' => DB::table('countries AS c')
                ->select('c.id', 'c.formatted_name AS name')
                ->orderBy('c.formatted_name', 'asc')
                ->get(),
            'locales' => DB::table('locales AS lo')->select('id', 'language AS name')->orderBy('lo.language', 'asc')->get(),
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
            'countries_id'       => 'required',
            'locales_id'         => 'required',
            'name'               => 'required',
            'direction'          => 'required',
        ], [
            'countries_id.required' => 'Country is required', // custom message
            'locales_id.required' => 'Locale is required', // custom message
            'name.required' => 'Name is required', // custom message
            'direction.required' => 'Direction is required', // custom message
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $language = new Language();

            $language->name = $request->name;
            $language->countries_id = $request->countries_id;
            $language->locales_id = $request->locales_id;
            $language->direction = $request->direction;

            $language->default = determineBool($request->default);
            $language->active = determineBool($request->active);
            $language->available = determineBool($request->available);

            $language->save();

            if ($language->available && !$language->active) {
                throw new Exception('You cannot set a language to "Available on web" when it\'s not "Active"!');
            }

            if ($language->default) {
                $langDefault = $this->languageService->setDefaultLang($language->id);

                if (!$langDefault['success']) {
                    throw new Exception($langDefault['error']['message'], $langDefault['error']['code']);
                }
            }

            $languagesCount = Language::where(['default' => true])->count();

            if (!$languagesCount) {
                throw new Exception("You must have one default language! Please set another language as default and try again.", 400);
            }

            DB::commit();

            return $this->successResponse([
                'msg' => 'Created successfully!',
                'languages' => $this->settingsService->getLanguages(false)
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
            'language' => Language::find($id),
            'countries' => DB::table('countries AS c')
                ->select('c.id', 'c.formatted_name AS name')
                ->orderBy('c.formatted_name', 'asc')
                ->get(),
            'locales' => DB::table('locales AS lo')->select('id', 'language AS name')->orderBy('lo.language', 'asc')->get(),
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
        $language = Language::find($id);

        $originalDefault = determineBool($language->default);

        $validator = Validator::make($request->all(), [
            'countries_id'       => 'required',
            'locales_id'         => 'required',
            'name'               => 'required',
            'direction'          => 'required',
        ], [
            'countries_id.required' => 'Country is required', // custom message
            'locales_id.required' => 'Locale is required', // custom message
            'name.required' => 'Name is required', // custom message
            'direction.required' => 'Direction is required', // custom message
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $language->name = $request->name;
            $language->countries_id = $request->countries_id;
            $language->locales_id = $request->locales_id;
            $language->direction = $request->direction;

            $language->default = determineBool($request->default);
            $language->active = determineBool($request->active);
            $language->available = determineBool($request->available);

            $language->save();

            if ($language->available && !$language->active) {
                throw new Exception('You cannot set a language to "Available on web" when it\'s not "Active"!');
            }

            if ($originalDefault && !$language->active) {
                throw new Exception('You cannot deactivate the default language. Please set another language as default and try again');
            }

            if ($language->default) {
                $langDefault = $this->languageService->setDefaultLang($language->id);

                if (!$langDefault['success']) {
                    throw new Exception($langDefault['error']['message'], $langDefault['error']['code']);
                }
            }

            $languagesCount = Language::where(['default' => true])->count();

            if (!$languagesCount) {
                throw new Exception("You must have one default language! Please set another language as default and try again.", 400);
            }

            DB::commit();

            return $this->successResponse([
                'msg' => 'Updated successfully!',
                'languages' => $this->settingsService->getLanguages(false)
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
            $language = Language::find($id);
            $languageName = $language->name;
            $language->delete();

            $languagesCount = Language::where(['default' => true])->count();

            if (!$languagesCount) {
                throw new Exception("You must have one default language! Please set another language as default and try again.", 400);
            }

            DB::commit();
            return $this->successResponse([
                'msg' => $languageName . ' language was successfully deleted'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function generalTranslations(Request $request)
    {
        $generalTranslations = $this->languageService->getGeneralTranslations();

        if (!$generalTranslations['success']) {
            return $this->errorResponse($generalTranslations['error']['message'], $generalTranslations['error']['code']);
        }

        $records = $generalTranslations['general_translations'];

        $filters = $request->get('filters', []);

        foreach ($filters as $k => $filter) {
            switch ($k) {
                case 'global':
                    $records =  array_filter($records, function ($item) use ($filter) {
                        return str_contains($item['code'], $filter['value'])
                            || str_contains($item['location'], $filter['value'])
                            || str_contains($item['name'], $filter['value'])
                            || str_contains($item['text'], $filter['value']);
                    });
                    break;
            }
        }

        $data = $this->paginate($records, $request->itemsPerPage ?? 10, $request->page ?? 1)->toArray();

        $recordsData =  $data['data'];
        $totalRecords =  $data['total'];

        return $this->successResponse([
            'records' => array_values($recordsData),
            'totalRecords' => $totalRecords,
        ]);
    }

    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showGeneralTranslation($id)
    {
        $query = DB::table('language_translation AS lt')
            ->leftJoin('language AS l', 'lt.language_id', '=', 'l.id')
            ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
            ->leftJoin('language_code AS lc', 'lc.id', '=', 'lt.language_code_id')
            ->select(
                'lc.id AS id',
                'lc.code AS code',
                'lc.is_html AS is_html',
            )
            ->where('lc.id', $id);

        $translation = $query->first();

        if (!$translation) {
            return $this->errorResponse('Record not found!', 404);
        }

        $translation->is_html = $translation->is_html !== 0;

        return $this->successResponse([
            'translation' => $translation,
            'translations' => getCodesTranslations([$translation->code])
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateGeneralTranslation(Request $request)
    {
        // Validate the data
        $validator = Validator::make($request->all(), [
            'code'       => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            LanguageCode::where('id', $request->id)->update([
                'is_html' => isset($request->is_html) && ($request->is_html === true || $request->is_html === 'true')
            ]);

            $this->translationService->translateFields(['code'], $request->all());

            DB::commit();
            return $this->successResponse([
                'msg' => 'Translation updated successfully!',
                'data' => $request->all()
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    public function publishLanguages()
    {
        try {
            $this->languageService->publishLanguages();

            return $this->successResponse([
                'msg' => 'Languages published successfully!'
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }
}
