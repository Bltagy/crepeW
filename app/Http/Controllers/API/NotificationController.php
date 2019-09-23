<?php
namespace App\Http\Controllers\API;
use App\DeviceToken;
use App\Http\Controllers\API\BaseController as BaseController;
use App\User;
use App\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class NotificationController extends BaseController {

	/**
	 * @OA\Post(
	 *   path="/submitToken",
	 *   tags={"User"},
	 *   summary="Submit user device token ",
	 *   description="",
	 *   operationId="submitToken",
	 *   @OA\Parameter(
	 *     name="token",
	 *     description="Device token",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="device_id",
	 *     description="User device id",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="user_id",
	 *     description="User ID if available",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="ios",
	 *     description="set 1 in case IOS device otherwise you can set 0 or not sending it at all",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="X-fcmData",
	 *     description="Important!!! This should add to header in case you can accept FCM data should be like (X-fcmData :1 ) or  (X-fcmData :0 ) or you able to not send it.This will add to the orders notifications ( type : orderStatus ) beside title and body",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="Token update/saved",
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
	public function storeToken(Request $request) {
		$validator = Validator::make($request->all(), [
			'token'     => 'required',
			'device_id' => 'required',
		]);
		$fcmData = ($request->header('X-fcmData')) ?? null;

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();
		$token = DeviceToken::where('device_id', $input['device_id'])->first();

		if ($token) {
			$token->token = $input['token'];
			if (!empty($input['user_id'])) {
				$token->user_id = $input['user_id'];
			}
			$token->fcmData = $fcmData;
			$token->ios     = (!empty($input['ios']) ? 1 : 0);
			$token->save();
			return $this->sendResponse('', 'User device tokens has been updated successfully.');
		} else {
			$token            = new DeviceToken;
			$token->device_id = $input['device_id'];
			$token->token     = $input['token'];
			if (!empty($input['user_id'])) {
				$token->user_id = $input['user_id'];
			}
			$token->fcmData = $fcmData;
			$token->ios     = (!empty($input['ios']) ? 1 : 0);
			$token->save();
			return $this->sendResponse('', 'User device tokens has been saved successfully.');
		}
	}

	/**
	 * @OA\Post(
	 *   path="/logout",
	 *   tags={"User"},
	 *   summary="logout user and delete his device id.",
	 *   description="",
	 *   operationId="logout",
	 *   @OA\Parameter(
	 *     name="device_id",
	 *     description="User device id",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="Token has been deleted successfully",
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
	public function logout(Request $request) {
		$validator = Validator::make($request->all(), [
			'device_id' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$input = $request->all();
		$token = DeviceToken::where('device_id', $input['device_id'])->delete();

		return $this->sendResponse('', 'Token has been deleted successfully.');

	}

	/**
	 * Register api
	 *
	 * @return \Illuminate\Http\Response
	 */
	public static function store($data) {
		if (empty($data['user_id'])) {
			return false;
		}
		$nofication          = new UserNotification;
		$nofication->user_id = $data['user_id'];
		if ($data['title']) {
			$nofication->title = $data['title'];
		}
		if ($data['body']) {
			$nofication->body = $data['body'];
		}

		if (isset($data['image'])) {
			$nofication->photo = $data['image'];
		}
		$nofication->save();
	}

	/**
	 * @OA\Get(
	 *   path="/userNotification/{id}",
	 *   tags={"User"},
	 *   summary="Retrieve user notifications",
	 *   operationId="userNotification",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User Id",
	 *     in="path",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="integer"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="

	 *     ",
	 *   )
	 * )
	 */

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function userNotification($id) {

		$notifications = UserNotification::where('user_id', $id)->get();
		$notifications = $notifications->map(function ($item, $key) {
			if ($item['photo']) {
				$item['photo'] = url('/images/' . $item['photo']);
			}
			return $item;

		});
		return response()->json($notifications)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => UserNotification::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function sendNotification(Request $request) {
		$input = $request->all();

		$image = $request->get('image');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$image = $name;
		} else {
			$image = null;
		}

		$tokens_array    = DeviceToken::whereNotNull('token')->where('ios', '!=', 1)->pluck('token')->all();
		$tokens_user_ids = DeviceToken::whereNotNull('token')->where('ios', '!=', 1)->pluck('token', 'user_id')->all();
		$fcmData         = [
			'title' => $input['title'],
			'body'  => $input['body'],
			'type'  => 'global',
		];

		$result = [];
		if (!empty($tokens_array)) {
			$result['android'] = \PushNotification::setService('fcm')
				->setMessage([
					'data' => $fcmData,
				])
				->setDevicesToken($tokens_array)
				->send()
				->getFeedback();
			$this->StoreNotification($tokens_user_ids, $fcmData, $image);
		}

		//*	IOS
		$tokens_array_ios    = DeviceToken::whereNotNull('token')->where('ios', 1)->pluck('token')->all();
		$tokens_user_ids_ios = DeviceToken::whereNotNull('token')->where('ios', 1)->pluck('token', 'user_id')->all();
		$fcmData             = [
			'title' => $input['title'],
			'body'  => $input['body'],
			'type'  => 'global',
		];

		if (!empty($tokens_array)) {
			$result['ios'] = \PushNotification::setService('fcm')
				->setMessage([
					'notification' => [
						'title' => $input['title'],
						'body'  => $input['body'],
					],
					'data'         => $fcmData,
				])
				->setDevicesToken($tokens_array_ios)
				->send()
				->getFeedback();
			$this->StoreNotification($tokens_user_ids_ios, $fcmData, $image);
		}

		/*=====  End of For Android  ======*/

		return response()->json($result);
	}

	public function sendNotificationSingle(Request $request) {
		$input = $request->all();

		$image = $request->get('image');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$image = $name;
		} else {
			$image = null;
		}

		$tokens_array    = DeviceToken::where('user_id', $input['user_id'])->whereNotNull('token')->where('ios', '!=', 1)->pluck('token')->all();
		$tokens_user_ids = DeviceToken::where('user_id', $input['user_id'])->whereNotNull('token')->where('ios', '!=', 1)->pluck('token', 'user_id')->all();
		$fcmData         = [
			'title' => $input['title'],
			'body'  => $input['body'],
			'type'  => 'global',
		];

		$result = [];
		if (!empty($tokens_array)) {
			$result['android'] = \PushNotification::setService('fcm')
				->setMessage([
					'data' => $fcmData,
				])
				->setDevicesToken($tokens_array)
				->send()
				->getFeedback();
			$this->StoreNotification($tokens_user_ids, $fcmData, $image);
		}

		//*	IOS
		$tokens_array_ios    = DeviceToken::where('user_id', $input['user_id'])->whereNotNull('token')->where('ios', 1)->pluck('token')->all();
		$tokens_user_ids_ios = DeviceToken::where('user_id', $input['user_id'])->whereNotNull('token')->where('ios', 1)->pluck('token', 'user_id')->all();
		$fcmData             = [
			'title' => $input['title'],
			'body'  => $input['body'],
			'type'  => 'global',
		];

		if (!empty($tokens_array_ios)) {
			$result['ios'] = \PushNotification::setService('fcm')
				->setMessage([
					'notification' => [
						'title' => $input['title'],
						'body'  => $input['body'],
					],
					'data'         => $fcmData,
				])
				->setDevicesToken($tokens_array_ios)
				->send()
				->getFeedback();
			$this->StoreNotification($tokens_user_ids_ios, $fcmData, $image);
		}

		/*=====  End of For Android  ======*/

		return response()->json($result);
	}

	public static function Send($title, $body, $user_id, $save = true, $type = 'global') {

		$tokens_array = DeviceToken::whereNotNull('token')
			->where('ios', '!=', 1)
			->where('user_id', $user_id)
			->pluck('token')
			->all();

		$fcmData = [
			'title' => $title,
			'body'  => $body,
			'type'  => $type,
		];

		$result = [];
		if (!empty($tokens_array)) {
			$result['android'] = \PushNotification::setService('fcm')
				->setMessage([
					'data' => $fcmData,
				])
				->setDevicesToken($tokens_array)
				->send()
				->getFeedback();
		}

		$tokens_array_ios = DeviceToken::whereNotNull('token')
			->where('ios', 1)
			->where('user_id', $user_id)
			->pluck('token')
			->all();

		if (!empty($tokens_array_ios)) {
			$result['ios'] = \PushNotification::setService('fcm')
				->setMessage([
					'notification' => [
						'title' => $title,
						'body'  => $body,
					],
					'data'         => $fcmData,
				])
				->setDevicesToken($tokens_array_ios)
				->send()
				->getFeedback();

		}

	}

	public function StoreNotification($users, $fcmData, $image) {
		foreach ($users as $key => $value) {

			$data = array('user_id' => $key,
				'body'                  => $fcmData['body'],
				'title'                 => $fcmData['title'],
				'image'                 => $image,
			);

			$this->store($data);
		}
	}

	public function listAll() {

		return response()->json(DeviceToken::all())->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => UserNotification::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

}
