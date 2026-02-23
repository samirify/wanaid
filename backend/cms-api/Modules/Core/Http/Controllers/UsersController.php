<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Core\Models\User;
use Modules\Core\Services\TranslationService;
use Modules\Core\Services\UserService;
use Modules\Core\Traits\MediaTrait;
use SoulDoit\DataTable\SSP;

class UsersController extends Controller
{
    use ValidatesRequests;
    use MediaTrait;
    use AppHelperTrait;
    use SAAApiResponse;

    public function __construct(
        private readonly UserService $userService,
        private readonly TranslationService $translationService,
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
            ['label' => 'ID',         'db' => 'id'],
            ['label' => 'Username',     'db' => 'username'],
            ['label' => 'Full Name',     'db' => 'full_name'],
            ['label' => 'Image ID',     'db' => 'media_store_id'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_users')
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
            'titles' => getTitles(),
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
            'username'      => 'required|unique:users,username,' . $request->username,
            'first_name'    => 'max:255',
            'middle_names'  => 'max:255',
            'last_name'     => 'max:255',
            'email'         => [
                'required',
                'email',
                'unique:email,email_address',
                Rule::unique('email', 'email_address')->where('is_primary', true),
            ],
            'user_image'    => 'nullable|max:' . config('client.images.max_upload_size') . '|mimes:' . config('client.images.allowed_mime_types'),
        ], [
            'first_name.max' => 'First Name must not be greater than 255 characters!',
            'middle_names.max' => 'Middle Name(s) must not be greater than 255 characters!',
            'last_name.max' => 'Last Name must not be greater than 255 characters!',
            'email.required' => 'Email is required',
            'email.unique' => 'Email ' . $request->email . ' is already in use.',
            'username.required' => 'Username is required.',
            'username.unique' => 'Username "' . $request->username . '" has already been taken.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        return $this->userService->createUser($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->errorResponse('User was not found!', 404);
        }

        return $this->successResponse([
            'user' => $user,
            'roles' => $user->roles()->get()->pluck('name'),
            'titles' => getTitles(),
            'translations' => getCodesTranslations([
                'USER_' . $user->id . '_FIRST_NAME',
                'USER_' . $user->id . '_MIDDLE_NAMES',
                'USER_' . $user->id . '_LAST_NAME',
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
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User was not found!', 404);
        }

        // Validate the data
        $validator = Validator::make($request->all(), [
            'username'         => [
                'required',
                Rule::unique('users', 'username')->whereNot('username', $user->username),
            ],
            'first_name'    => 'max:255',
            'middle_names'  => 'max:255',
            'last_name'     => 'max:255',
            'email'         => [
                'required',
                'email',
            ],
            'user_image'    => 'nullable|max:' . config('client.images.max_upload_size') . '|mimes:' . config('client.images.allowed_mime_types'),
        ], [
            'first_name.max' => 'First Name must not be greater than 255 characters!',
            'middle_names.max' => 'Middle Name(s) must not be greater than 255 characters!',
            'last_name.max' => 'Last Name must not be greater than 255 characters!',
            'email.required' => 'Email is required',
            'username.required' => 'Username is required.',
            'username.unique' => 'Username "' . $request->username . '" has already been taken.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        return $this->userService->updateUser($user, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->userService->deleteUser($id);
    }
}
