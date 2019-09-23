<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
return $request->user();
});
 */

Route::post('/login', 'API\AuthController@auth');

Route::post('/fbLogin', 'API\UserController@fbLogin');

Route::get('/forgetPassword', 'API\UserController@forgetPassword');
Route::get('/listTokens', 'API\NotificationController@listAll');

Route::get('/productAdditions/{id}', 'API\ProductsAdditionController@productAdditions');

Route::get('/additions', 'API\ProductsAdditionController@index');

Route::post('/changePassword', 'API\UserController@changePassword');

Route::post('/submitToken', 'API\NotificationController@storeToken');

Route::post('/logout', 'API\NotificationController@logout');

Route::resource('homeData', 'API\HomeDataController');

Route::get('statistics', 'API\HomeDataController@statistics');

Route::post('register', 'API\UserController@store');

Route::get('homeSlider', 'API\HomeSliderController@index');

Route::post('resendCode', 'API\UserController@resendCode');

Route::post('userConfirm', 'API\UserController@userConfirm');

Route::post('SendContactUs', 'API\ContactUsController@send');

Route::get('products', 'API\ProductController@index');

Route::get('sync', 'API\SyncController@index');

Route::resource('productsAddition', 'API\ProductsAdditionController');

Route::resource('productsAdditionCategory', 'API\ProductsAdditionCategoryController');

Route::get('Products', 'API\ProductController@indexAddition');

Route::get('areas', 'API\AreaController@index');

Route::get('productsByCategories', 'API\ProductController@productsByCategories');

Route::get('products/{id}', 'API\ProductController@show');
Route::get('checkMobileNo/{mobile_number}', 'API\UserController@checkMobileNo');

Route::get('prodcutsNcategories', 'API\ProductCategoryController@prodcutsNcategories');

Route::get('verifyPromoCode/{promo_code}', 'API\PromoCodeController@verifyPromoCode');
Route::post('sendNotificationSingle', 'API\NotificationController@sendNotificationSingle');
	Route::post('submitOrder', 'API\OrderController@submitOrder');
Route::middleware('auth:api')->group(function () {

	Route::post('saveSort', 'API\ProductCategoryController@saveSort');


	Route::resource('gallery', 'API\GalleryController');

	Route::get('areas/{id}', 'API\AreaController@show');
	Route::put('areas/{id}', 'API\AreaController@update');

	Route::post('submitRate', 'API\ProductController@submitRate');

	Route::post('delProductPhoto/{id}', 'API\ProductController@delProductPhoto');

	Route::post('products', 'API\ProductController@store');

	Route::post('homeSlider', 'API\HomeSliderController@store');

	Route::get('homeSlider/{id}', 'API\HomeSliderController@show');

	Route::get('userNotification/{id}', 'API\NotificationController@userNotification');

	Route::put('homeSlider/{id}', 'API\HomeSliderController@update');

	Route::delete('homeSlider/{id}', 'API\HomeSliderController@destroy');

	Route::put('products/{id}', 'API\ProductController@update');

	Route::delete('products/{id}', 'API\ProductController@destroy');

	Route::resource('productCategory', 'API\ProductCategoryController');

	Route::resource('contactUs', 'API\ContactUsController');

	Route::get('checkToken', 'API\UserController@checkToken');

	Route::resource('profile', 'API\UserController');

	Route::put('removePhoto/{id}', 'API\UserController@removePhoto');

	Route::resource('users', 'API\UserController');

	Route::get('orders', 'API\OrderController@index');

	Route::get('orders/{id}', 'API\OrderController@show');

	Route::put('orders/{id}', 'API\OrderController@update');

	Route::post('order', 'API\OrderController@store');

	Route::post('serviceRate', 'API\OrderController@serviceRate');

	Route::get('order/user/{id}', 'API\OrderController@history');

	Route::get('orderHistory/{user_id}', 'API\OrderController@orderHistory');

	Route::post('sendNotification', 'API\NotificationController@sendNotification');

	Route::resource('promoCodes', 'API\PromoCodeController');
});
