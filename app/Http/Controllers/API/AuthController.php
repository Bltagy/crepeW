<?php
namespace App\Http\Controllers\Api;

use App\User;
use Psr\Http\Message\ServerRequestInterface;
use \Laravel\Passport\Http\Controllers\AccessTokenController;

class AuthController extends AccessTokenController {

	/**
	 * @OA\Post(
	 *   path="/login",
	 *   tags={"User"},
	 *   summary="User login",
	 *   description="",
	 *   operationId="login",
	 *   @OA\Parameter(
	 *     name="username",
	 *     description="User email",
	 *     in="query",
	 *     required=true,
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
	 *     name="grant_type",
	 *     description="static always = 'password'",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="client_id",
	 *     description="Auth client ID and it is static = '2'",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="client_secret",
	 *     description="predefined static value = 'YLSUrWO0K16vbbcEJgsna7KBdhQrEwT5zyYcf3ZK'",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *    @OA\Response(response="200",
	 *     description="The User object with token to use it on SMS verification message",
	 *   ),
	 *    @OA\Response(response="401",
	 *     description="Invalid credentials",
	 *   ),
	 *    @OA\Response(response="405",
	 *     description="User not confirmed",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	public function auth(ServerRequestInterface $request) {
		$tokenResponse = parent::issueToken($request);
		$token         = $tokenResponse->getContent();

		// $tokenInfo will contain the usual Laravel Passort token response.
		$tokenInfo = json_decode($token, true);
		if (!empty($tokenInfo['error'])) {
			return $tokenResponse;
		}

		$username = $request->getParsedBody()['username'];

		
		$user     = User::whereEmail($username)->first();
		if ( empty($user) ){
			$user     = User::whereMobileNumber($username)->first();
		}
		

		if ($user->confirmed != 1) {
			$response = [
				'success' => false,
				'message' => 'User not confirmed',
			];

			$response['data']['id'] = $user->id;

			return response()->json($response, 405);
		}
		
		if ( !empty($request->getParsedBody()['admin']) && ($user->is_admin != 1 && $user->is_sub_admin != 1)) {
			$is_admin = 0;
			$response = [
				'success' => false,
				'message' => 'This user is not an Administrator!',
			];

			$response['data']['id'] = $user->id;

			return response()->json($response, 403);
		}

		$tokenInfo = collect($tokenInfo);
		unset(  $user->is_admin );
		$tokenInfo->put('user', $user);

		return $tokenInfo;
	}
}