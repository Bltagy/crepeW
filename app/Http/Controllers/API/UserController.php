<?php

namespace App\Http\Controllers\API;
use App\Area;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use App\Http\Traits\SMS;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends BaseController {
	use SMS, LangData;

	public function checkToken(Request $request) {
		return response()->json([])->withHeaders([
			'Content-Range'                 => 'users 0-1/1',
			'X-Total-Count'                 => User::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$input = $request->all();

		$skip    = ($input['_start']) ?? 0;
		$sort    = ($input['_sort']) ?? 'id';
		$order   = ($input['_order']) ?? 'ASC';
		$end     = ($input['_end']) ?? 10;
		$id_like = ($input['id_like']) ?? false;
		$q       = ($input['q']) ?? false;

		if ($id_like) {
			$id_like = explode('|', $id_like);
			$users   = User::whereIn('id', $id_like);
		} elseif ($sort == 'orders_count') {

			$users = User::limit(($end - $skip))->skip($skip);
		} else {
			$users = User::limit(($end - $skip))->skip($skip)->orderBy($sort, $order);
		}
		if ($q) {
			$users = $users->OrWhere('name', 'like', '%' . $q . '%')
				->OrWhere('mobile_number', 'like', '%' . $q . '%')
				->OrWhere('email', 'like', '%' . $q . '%')
				->OrWhere('id', $q);
		}
		if (isset($input['id'])) {
            $uri = $request->getRequestUri();
            $uri = str_replace('/api/users?id=', '', $uri);
            $uri = str_replace('&id=', ',', $uri);
            $idsARRAY = explode(',', $uri);
//
        }

		if ( isset($input['id']) && !empty($idsARRAY) ){

                $users = $users->whereIn('id', $idsARRAY);
//                dd($idsARRAY);
            }

		$users = $users->get();
		if ($sort == 'orders_count' && $order = 'ASC') {
			$users->sortBy(function ($q) {
				return $q->orders->count();
			});
		}

		if ($sort == 'orders_count' && $order = 'DESC') {
			$users->sortByDesc(function ($q) {
				return $q->orders->count();
			});
		}

		$users = $users->map(function ($item, $key) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/' . $item['photo']);
			} else {
				$item['photo'] = url('/images/c-logo.png');
			}
			$item['orders_count'] = $item['orders']->count();
			unset($item['orders']);
			return $item;

		});

		return response()->json($users)->withHeaders([
			'Content-Range'                 => 'users 0-1/1',
			'X-Total-Count'                 => User::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * @OA\Post(
	 *   path="/register",
	 *   tags={"User"},
	 *   summary="Register new user",
	 *   description="",
	 *   operationId="register",
	 *   @OA\Parameter(
	 *     name="name",
	 *     description="User full name",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="email",
	 *     description="User email",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="photo",
	 *     description="User photo base64",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="password",
	 *     description="Chosen password",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="c_password",
	 *     description="Chosen password confirmation",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="address",
	 *     description="User address",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="home_address",
	 *     description="User second home address",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="office_address",
	 *     description="User office address",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="mobile_number",
	 *     description="User mobile number",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="gender",
	 *     description="User gender (male or female) ",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="enum"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="landline_number",
	 *     description="User land line number",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User object with token to use it on SMS verification message",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$web       = ($request->header('client-type')) ?? false;
		$lang      = ($request->header('X-lang')) ?? 'en';
		$validator = Validator::make($request->all(), [
			'name'          => 'required',
			'email'         => 'required|email|unique:users',
			'mobile_number' => 'required|unique:users',
			'password'      => 'required',
			'c_password'    => 'required|same:password',
		],
			[
				'email.unique'         => \Lang::get('auth.email_already_exists', [], $lang),
				'mobile_number.unique' => \Lang::get('auth.mobile_number_already_exists', [], $lang),
			]);

		if ($validator->fails()) {
			$errors        = $validator->errors();
			$errorResponse = [
				'status'     => 'error',
				'error_data' => ['error_text' => implode(' ', $errors->all())],
			];
			return response()->json($errorResponse, 422);
		}

		$input          = $request->all();
		$user           = new User;
		$user->password = bcrypt($input['password']);

		if (!empty($input['name'])) {
			$user->name = $input['name'];
		}

		if (!empty($input['email'])) {
			$user->email = $input['email'];
		}

		if (!empty($input['landline_number'])) {
			$user->landline_number = $input['landline_number'];
		}

		if (!empty($input['mobile_number'])) {
			$mobile_number = $this->convert2english($input['mobile_number']);
			$user->mobile_number = $mobile_number;
		}

		if (!empty($input['address'])) {
			$user->address = $input['address'];
		}

		if (!empty($input['gender'])) {
			$user->gender = $input['gender'];

		}

		if (!empty($input['home_address'])) {
			$user->address = $input['home_address'];

		}
		if (!empty($input['office_address'])) {
			$user->address = $input['address'];
		}

		if (!empty($input['address'])) {
			$user->address = $input['address'];
		}

		if ($request->get('photo')) {
			$image = $request->get('photo');
			$name  = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/profiles/') . $name);
			$user->photo = $name;

		}

		if ($web) {
			if (empty($input['is_sub_admin'])) {
				$user->is_sub_admin = 0;
			} else {
				$user->is_sub_admin = 1;
			}

			if (empty($input['is_admin'])) {
				$user->is_admin = 0;
			} else {
				$user->is_admin = 1;
			}

			if (empty($input['confirmed'])) {
				$user->confirmed = 0;
			} else {
				$user->confirmed = 1;
			}

		} else {
			$user->confirmed = rand(1000, 9999);
		}
		$user->save();

		$success['token']   = $user->createToken('MyApp')->accessToken;
		$success['name']    = $user->name;
		$success['email']   = $user->email;
		$success['address'] = $user->address;
		$success['id']      = $user->id;
		$success['user']    = $user;

		$massege = "Your confirmation code is $user->confirmed .";
		$this->sendSMS($user->mobile_number, $massege);

		return $this->sendResponse($success, 'User register successfully.');
	}
	/**
	 * @OA\Post(
	 *   path="/fbLogin",
	 *   tags={"User"},
	 *   summary="Facebook user register or login.",
	 *   description="",
	 *   operationId="fbLogin",
	 *   @OA\Parameter(
	 *     name="fb_id",
	 *     description="Facebook user id",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="name",
	 *     description="User full name",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="email",
	 *     description="User email",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *
	 *   @OA\Parameter(
	 *     name="address",
	 *     description="User address",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="mobile_number",
	 *     description="User mobile number",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="landline_number",
	 *     description="User land line number",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User object with token to use it on SMS verification message",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function fbLogin(Request $request) {
		$validator = Validator::make($request->all(), [
			'fb_id' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();
		$user  = User::where('fb_id', $input['fb_id'])->first();

		if (!$user) {
			$user = new User;

			if (!empty($input['fb_id'])) {
				$user->fb_id = $input['fb_id'];
			}
			if (!empty($input['name'])) {
				$user->name = $input['name'];
			}

			if (!empty($input['email']) && !User::where('email', $input['email'])->count()) {
				$user->email = $input['email'];
			}

			if (!empty($input['landline_number'])) {
				$user->landline_number = $input['landline_number'];
			}

			if (!empty($input['mobile_number'])) {
				$mobile_number = $this->convert2english($input['mobile_number']);
				$user->mobile_number = $mobile_number;
				$user->confirmed     = 1;
			}

			if (!empty($input['address'])) {
				$user->address = $input['address'];
			}

			$user->save();
		}

		$success['token']         = $user->createToken('MyApp')->accessToken;
		$success['name']          = $user->name;
		$success['id']            = $user->id;
		$success['confirmed']     = ($user->confirmed == 1) ? "1" : "0";
		$success['is_completed']  = (!empty($user->mobile_number)) ? true : false;
		$success['mobile_number'] = $user->mobile_number;

		return $this->sendResponse($success, 'User register/logged successfully.');
	}

	/**
	 * @OA\Post(
	 *   path="/userConfirm",
	 *   tags={"User"},
	 *   summary="Confirm user using sms 4 digits",
	 *   description="Authentication required on header using token",
	 *   operationId="userConfirm",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User ID ( already retrieved on login or register requests )",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="code",
	 *     description="the 4 digits code that the user got on his mobile number",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User object with token to use it on SMS verification message",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function userConfirm(Request $request) {
		$lang      = ($request->header('X-lang')) ?? 'en';
		$validator = Validator::make($request->all(), [
			'id'   => 'required',
			'code' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();
		$user  = User::find($input['id']);

		if ($user->confirmed == 1) {
			return $this->sendError(\Lang::get('auth.confirmed_already', [], $lang));
		}

		if ($user->confirmed != $input['code']) {
			return $this->sendError(\Lang::get('auth.wrong_confirmation_code', [], $lang));
		}

		$user->confirmed = 1;
		$user->save();

		$success['token'] = $user->createToken('MyApp')->accessToken;
		$success['name']  = $user->name;
		$success['id']    = $user->id;
		return $this->sendResponse($success, \Lang::get('auth.confirmed_successfully', [], $lang));
	}

	/**
	 * @OA\Post(
	 *   path="/resendCode",
	 *   tags={"User"},
	 *   summary="Re send the sms confirmation code",
	 *   description="Authentication required on header using token",
	 *   operationId="resendCode",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User ID ( already retrieved on login or register requests )",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function resendCode(Request $request) {
		$lang      = ($request->header('X-lang')) ?? 'en';
		$validator = Validator::make($request->all(), [
			'id' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();
		$user  = User::find($input['id']);
		if (!$user) {
			return $this->sendError('User Not found.');
		}

		if ($user->confirmed == 1) {
			return $this->sendError('User already confirmed!');
		}

		$user->confirmed = rand(1000, 9999);
		$massege         = "Your confirmation code is $user->confirmed .";
		// $user->confirmed = 1234;
		$user->save();
		$this->sendSMS($user->mobile_number, $massege);
		return $this->sendResponse('', \Lang::get('auth.code_has_been_sent', [], $lang));
	}

	/**
	 * @OA\Get(
	 *   path="/profile/{id}",
	 *   tags={"User"},
	 *   summary="Get user profile by name",
	 *   description="",
	 *   operationId="profile",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User ID",
	 *     in="path",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User object including the areas list to make it easy.",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	public function show(Request $request, $id) {
		$user = User::find($id);
		$web  = ($request->header('client-type')) ?? false;
		$lang = ($request->header('X-lang')) ?? 'en';

		if (is_null($user)) {
			return $this->sendError('User not found.');
		}

		if ($user->photo) {
			$user->photo = url('/images/profiles/' . $user->photo);
		}
		$areas       = Area::all();
		$user->areas = $this->toLang($lang, $areas);
		if ($web) {
			if (!empty($user->address)) {
				$area          = explode('   ', $user->address);
				$areaName      = (Area::find($area[0])) ? Area::find($area[0])->name : "";
				$user->address = str_replace($area[0] . '   ', $areaName . '   ', $user->address);
				$imp           = explode('   ', $user->address);
				$user->address = (isset($imp[0])) ? "[Area]: $imp[0]" : '';
				$user->address .= (isset($imp[1])) ? "[Street Name]: $imp[1]" : '';
				$user->address .= (isset($imp[2])) ? "[Building No]: $imp[2]" : '';
				$user->address .= (isset($imp[3])) ? "[Floor]: $imp[3]" : '';
				$user->address .= (isset($imp[4])) ? "[Special sign]: $imp[4]" : '';
				$user->address .= (isset($imp[5])) ? "[Description]: $imp[5]" : '';

			} else {
				$user->address = null;
			}

			if (!empty($user->home_address)) {
				$area            = explode('   ', $user->home_address);
				$areaName        = Area::find($area[0])->name;
				$user->address_2 = str_replace($area[0] . '   ', $areaName . '   ', $user->home_address);
				$imp             = explode('   ', $user->address_2);
				$user->address_2 = (isset($imp[0])) ? "[Area]: $imp[0]" : '';
				$user->address_2 .= (isset($imp[1])) ? "[Street Name]: $imp[1]" : '';
				$user->address_2 .= (isset($imp[2])) ? "[Building No]: $imp[2]" : '';
				$user->address_2 .= (isset($imp[3])) ? "[Floor]: $imp[3]" : '';
				$user->address_2 .= (isset($imp[4])) ? "[Special sign]: $imp[4]" : '';
				$user->address_2 .= (isset($imp[5])) ? "[Description]: $imp[5]" : '';
			} else {
				$user->address_2 = null;
			}

			if (!empty($user->office_address)) {
				$area            = explode('   ', $user->office_address);
				$areaName        = Area::find($area[0])->name;
				$user->address_3 = str_replace($area[0] . '   ', $areaName . '   ', $user->office_address);
				$imp             = explode('   ', $user->address_3);
				$user->address_3 = (isset($imp[0])) ? "[Area]: $imp[0]" : '';
				$user->address_3 .= (isset($imp[1])) ? "[Street Name]: $imp[1]" : '';
				$user->address_3 .= (isset($imp[2])) ? "[Building No]: $imp[2]" : '';
				$user->address_3 .= (isset($imp[3])) ? "[Floor]: $imp[3]" : '';
				$user->address_3 .= (isset($imp[4])) ? "[Special sign]: $imp[4]" : '';
				$user->address_3 .= (isset($imp[5])) ? "[Description]: $imp[5]" : '';
			} else {
				$user->address_3 = null;
			}
		}
		if ($web) {
			if ($user->confirmed == 1) {
				$user->confirmed = true;
			} else {
				$user->confirmed = false;
			}

			if ($user->is_admin == 1) {
				$user->is_admin = true;
			} else {
				$user->is_admin = false;
			}

			if ($user->is_sub_admin == 1) {
				$user->is_sub_admin = true;
			} else {
				$user->is_sub_admin = false;
			}
		}
		return response()->json($user)->withHeaders([
			'Content-Range'                 => 'users 0-1/1',
			'X-Total-Count'                 => User::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * @OA\Put(
	 *   path="/profile/{id}",
	 *   tags={"User"},
	 *   summary="update user profile",
	 *   description="",
	 *   operationId="profile_udpate",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User ID",
	 *     in="path",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="name",
	 *     description="User full name",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="email",
	 *     description="User email",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="oldPassword",
	 *     description="In case changing password",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   )
	 *   ,
	 *   @OA\Parameter(
	 *     name="password",
	 *     description="Chosen password In case changing password",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   )
	 *   ,
	 *   @OA\Parameter(
	 *     name="c_password",
	 *     description="Chosen password confirmation",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="photo",
	 *     description="User photo base64",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="address",
	 *     description="User address",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="home_address",
	 *     description="User second home address",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="office_address",
	 *     description="User office address",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="gender",
	 *     description="User gender (male or female) ",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="enum"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="mobile_number",
	 *     description="User mobile number",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="landline_number",
	 *     description="User land line number",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User object with token to use it on SMS verification message",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {

		$user  = User::findOrFail($id);
		$web   = ($request->header('client-type')) ?? false;
		$lang  = ($request->header('X-lang')) ?? 'en';
		$input = $request->all();

		if (!empty($input['name'])) {
			$user->name = $input['name'];
		}

		if (!empty($input['email'])) {
			$user->email = $input['email'];
		}

		if (!empty($input['landline_number'])) {
			$user->landline_number = $input['landline_number'];
		}
		if (!empty($input['gender'])) {
			$user->gender = $input['gender'];

		}
		if (!empty($input['mobile_number'])) {
			$validator = Validator::make($request->all(), [
				'mobile_number' => 'required|unique:users,mobile_number,' . $id,
			]);

			if ($validator->fails()) {
				$errors        = $validator->errors();
				$errorResponse = [
					'status'     => 'error',
					'error_data' => ['error_text' => implode(' ', $errors->all())],
				];
				return response()->json($errorResponse, 422);
			}
			if (!$web && $user->mobile_number != $input['mobile_number']) {
				$mobile_number = $this->convert2english($input['mobile_number']);
				$user->mobile_number = $mobile_number;
				$user->confirmed     = rand(1000, 9999);
				$user->save();
				$massege = "Your confirmation code is $user->confirmed .";
				$this->sendSMS($user->mobile_number, $massege);
			}
		}

		if (!empty($input['address'])) {
			$user->address = $input['address'];

		}

		if (!empty($input['home_address'])) {
			$user->home_address = $input['home_address'];

		}
		if (!empty($input['office_address'])) {
			$user->office_address = $input['office_address'];
		}

		if ($web) {

			if (empty($input['is_sub_admin'])) {
				$user->is_sub_admin = 0;
			} else {
				$user->is_sub_admin = 1;
			}

			if (empty($input['is_admin'])) {
				$user->is_admin = 0;
			} else {
				$user->is_admin = 1;
			}

			if (empty($input['confirmed'])) {
				$user->confirmed = 0;
			} else {
				$user->confirmed = 1;
			}

		}

		$image = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/profiles/') . $name);
			$user->photo = $name;

		}

		$user->save();

		if (!empty($input['password']) && $web != 'web') {
			if (\Hash::check($input['oldPassword'], $user->password)) {
				$user->password = bcrypt($input['password']);
				$user->save();
			} else {
				return $this->sendError(\Lang::get('validation.wrong_current_password', [], $lang));
			}

		} elseif (!empty($input['password']) && $web == 'web') {
			$user->password = bcrypt($input['password']);
			$user->save();
		}
		// return $this->sendResponse($user, 'Your profile updated successfully.');
		$user->is_completed = (!empty($user->mobile_number)) ? true : false;
		$user->is_confirmed = (empty($user->confirmed)) ? "1" : "0";
		$user->confirmed    = (empty($user->confirmed)) ? "1" : "0";
		return response()->json($user)->withHeaders([
			'Content-Range'                 => 'users 0-1/1',
			'X-Total-Count'                 => 15,
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * @OA\Put(
	 *   path="/removePhoto/{id}",
	 *   tags={"User"},
	 *   summary="remove user photo",
	 *   description="",
	 *   operationId="removePhoto",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User ID",
	 *     in="path",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User photo has been removed",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function removePhoto(Request $request, $id) {
		$user  = User::findOrFail($id);
		$input = $request->all();

		$user->photo = null;
		$user->save();

		return response()->json($user)->withHeaders([
			'Content-Range'                 => 'users 0-1/1',
			'X-Total-Count'                 => 15,
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(User $user) {
		if ($user->id == 1) {
			return false;
		}
		$user->delete();

		return response()->json($user)->withHeaders([
			'Content-Range'                 => 'users 0-1/1',
			'X-Total-Count'                 => User::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * @OA\Get(
	 *   path="/forgetPassword",
	 *   tags={"User"},
	 *   summary="Forget password request",
	 *   description="",
	 *   operationId="forgetPassword",
	 *   @OA\Parameter(
	 *     name="mobile_number",
	 *     description="User mobile",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User",
	 *   ),
	 *    @OA\Response(response="404",
	 *     description="User not found",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	public function forgetPassword(Request $request) {

		$validator = Validator::make($request->all(), [
			'mobile_number' => 'required|exists:users',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();

		$user = User::where('mobile_number', $request->mobile_number)->first();

		$user->forget_code = rand(1000, 9999);
		$user->save();
		$massege = "Your forget password code is $user->forget_code .";
		$this->sendSMS($user->mobile_number, $massege);

		$user->save();

		return $this->sendResponse(['id' => $user->id], 'A SMS message sent to the user.');

	}

	/**
	 * @OA\Post(
	 *   path="/changePassword",
	 *   tags={"User"},
	 *   summary="Change user password using the SMS code",
	 *   description="Authentication required on header using token",
	 *   operationId="changePassword",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User ID ( already retrieved )",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),@OA\Parameter(
	 *     name="code",
	 *     description="The SMS code",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),@OA\Parameter(
	 *     name="password",
	 *     description="the new password Please not that your password confirmation should be from your side",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function changePassword(Request $request) {
		$validator = Validator::make($request->all(), [
			'id'       => 'required',
			'code'     => 'required',
			'password' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();

		$user = User::find($input['id']);

		if (!$user) {
			return $this->sendError('User doesn\'t exists.', '', 404);
		}

		if ($user->forget_code != $input['code']) {
			return $this->sendError('Invalid code.', '', 422);
		}

		$user->password = bcrypt($input['password']);

		$user->forget_code = 0;

		$user->save();
		$success          = [];
		$success['token'] = $user->createToken('MyApp')->accessToken;
		$success['name']  = $user->name;
		$success['id']    = $user->id;

		return $this->sendResponse($success, 'The user password has been updated successfully.');

	}
	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function checkMobileNo(Request $request, $mobile_number) {

		$user = User::where('email', $mobile_number);
		if (!empty($request->user_id)) {
			$user = $user->where('id', '!=', $request->user_id);
		}
		$user = $user->count();

		$err = [];
		if ($user) {
			$err['email'] = "exists";
			return $this->sendError('User email exists.', '', 422);
		}

		$userE = User::where('mobile_number', $mobile_number);
		if (!empty($request->user_id)) {
			$userE = $userE->where('id', '!=', $request->user_id);
		}
		$userE = $userE->count();
		if ($userE) {
			$err['mobile_number'] = "exists";
		}

		if (!empty($err)) {
			return $this->sendError('User mobile_number exists.', '', 422);
		}

		return $this->sendResponse([], 'email not exists.');
	}

	public function checkEmail($email) {
		$find1 = strpos($email, '@');
		$find2 = strpos($email, '.');
		return ($find1 !== false && $find2 !== false && $find2 > $find1);
	}

}
