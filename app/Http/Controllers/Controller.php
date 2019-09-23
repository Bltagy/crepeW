<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Crepe & Waffle API documentation",
 *         description="A documented API for the required requests.",
 *         @OA\Contact(
 *             email="ahmed.bltagy@appssquare.com"
 *         ),

 *     ),
 *     @OA\Server(
 *         description="Production Server",
 *         url="https://app.crepe-waffle.com/api"
 *     ),
 *    
 * )
 */

/**
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     description="Use a global client_id / client_secret and your username / password combo to obtain a token",
 *     name="Password Based",
 *     in="header",
 *     scheme="https",
 *     securityScheme="Password Based",
 *     @OA\Flow(
 *         flow="password",
 *         authorizationUrl="/api/login",
 *         tokenUrl="/api/login",
 *         scopes={}
 *     )
 * )
 */


/**
 * @OA\Tag(
 *   name="User",
 *   description="Every thing related to user",
 * )

 */


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
