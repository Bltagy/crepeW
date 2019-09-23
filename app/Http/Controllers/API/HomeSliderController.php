<?php

namespace App\Http\Controllers\API;

use App\HomeSlider;
use App\ServiceRate;
use App\Order;
use App\HomeData;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Product;
use Illuminate\Http\Request;
use Validator;

/**
 * @OA\Get(
 *   path="/homeSlider",
 *   tags={"Sliders&gallery"},
 *   summary="Retrieve home sliders for website and app",
 *   description="",
 *   operationId="homeSlider",
 *    @OA\Response(response="200",
 *     description="",
 *   ),
 *   @OA\Response(response="422",description="Validation Error"),
 *   security={{
 *     "petstore_auth": {"write:pets", "read:pets"}
 *   }}
 * )
 */

class HomeSliderController extends BaseController {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {

		$input = $request->all();

		$skip   = ($input['_start']) ?? 0;
		$sort   = ($input['_sort']) ?? 'id';
		$order  = ($input['_order']) ?? 'ASC';
		$take  = ($input['_end']) ?? 10;
		$q      = ($input['q']) ?? false;
		$price  = ($input['price']) ?? false;
		$android_version  = ($input['android_version']) ?? false;
		$ios_version  = ($input['ios_version']) ?? false;
		$lang   = ($request->header('X-lang')) ?? 'en';
		$web    = ($request->header('client-type')) ?? false;
		$mobile = ($request->header('x-Device')) ? $request->header('x-Device') : false;

		$response    = [];
		$homeSliders = HomeSlider::select('id', 'title', 'photo', 'photo_ar', 'for_mobile')->orderBy($sort, $order);

		if ($q) {
			$homeSliders = $homeSliders->Where('title', 'like', '%' . $q . '%')
				->OrWhere('id', $q);
		}

		if ($web) {
			$homeSliders = $homeSliders->limit(($take - $skip))->skip($skip)->get();
		} else {

			if ($mobile == 'mobile') {
				$homeSliders = $homeSliders->where('for_mobile', 1)->get();
			} else {
				$homeSliders = $homeSliders->where('for_mobile', 0)->get();
			}

		}
		if ($request->header('X-lang') == 'ar') {
			$homeSliders = $homeSliders->map(function ($item, $key) use ($mobile) {
				if (!empty($item['photo_ar'])) {
					$item['photo'] = url('/images/' . $item['photo_ar']);
				}else{
					$item['photo'] = $item['photo_ar'];
				}
				unset($item['photo_ar']);
				return $item;

			});
		} else {

			$homeSliders = $homeSliders->map(function ($item, $key) {
				if (!empty($item['photo'])) {
					$item['photo'] = url('/images/' . $item['photo']);
				}
				unset($item['photo_ar']);
				return $item;

			});
		}

		$featured = Product::where('featured', 1)->where('in_stock', 1)->get();
		if ($lang == 'ar') {
			$featured = $featured->map(function ($item, $key) use ($mobile) {

				if ($mobile) {
					if (!empty($item['mobile_photo_ar'])) {
						$item['photo'] = url('/images/' . $item['mobile_photo_ar']);
					}
				} else {
					if (!empty($item['photo_ar'])) {
						$item['photo'] = url('/images/' . $item['photo_ar']);
					}
				}
				$item['name']        = $item['name_ar'];
				$item['description'] = $item['description_ar'];
				unset($item['name_ar']);
				unset($item['mobile_photo_ar']);
				unset($item['mobile_photo']);
				unset($item['photo_ar']);
				unset($item['description_ar']);
				unset($item['in_stock']);
				unset($item['pos_id']);
				unset($item['in_stock']);
				return $item;

			});
		} else {
			$featured = $featured->map(function ($item, $key) use ($mobile) {

				if ($mobile) {
					if (!empty($item['mobile_photo'])) {
						$item['photo'] = url('/images/' . $item['mobile_photo']);
					}
				} else {
					if (!empty($item['photo'])) {
						$item['photo'] = url('/images/' . $item['photo']);
					}
				}
				unset($item['name_ar']);
				unset($item['mobile_photo_ar']);
				unset($item['mobile_photo']);
				unset($item['photo_ar']);
				unset($item['description_ar']);
				unset($item['in_stock']);
				unset($item['pos_id']);
				unset($item['in_stock']);
				return $item;

			});
		}
		$response['slider']      = $homeSliders;
		$response['most_common'] = $featured;
		$response['discount'] = HomeData::where('name', 'discount')->first()->value;

		$response['needRate'] = 0;
		if (!empty($input['user_id'])) {

			$latestOrder = Order::where('user_id', $input['user_id'])
						   ->where('order_status','Completed' )->latest()->first();
			if ($latestOrder) {
				$hasRate = ServiceRate::where('user_id', $input['user_id'])
					->where('order_id', $latestOrder->id)
					->count();
				if (!$hasRate) {
					$response['needRate'] = $latestOrder->id;
				}
			}
		}

		if ($request->header('Client-Type') == 'web') {
			return response()->json($homeSliders)->withHeaders([
				'Content-Range'                 => 'homeSliders 0-1/1',
				'X-Total-Count'                 => HomeSlider::count(),
				// 'Cache-Control'                 => 'max-age=604800, public',
				'Access-Control-Expose-Headers' => 'X-Total-Count',
			]);
		}
		$androidVersion = HomeData::where('name', 'android_version')->first()->value;
		$iosVersion = HomeData::where('name', 'ios_version')->first()->value;

		$androidMustUpdate = HomeData::where('name', 'android_must_update')->first()->value;
		$iosMustUpdate = HomeData::where('name', 'ios_must_update')->first()->value;

		$response['update_hint'] = 0;
		$response['must_update'] = 0;

		if ( $android_version && $android_version < $androidMustUpdate ){
			$response['must_update'] = 1;			
		}

		if ( $android_version && $android_version < $androidVersion ){
			$response['update_hint'] = 1;			
		}


		if ( $ios_version && $ios_version < $iosMustUpdate ){
			$response['must_update'] = 1;			
		}

		if ( $ios_version && $ios_version < $iosVersion ){
			$response['update_hint'] = 1;			
		}
		
		
		return response()->json($response)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => HomeSlider::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$input = $request->all();
		$validator = Validator::make($input, [
			'title' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$homeSlider = new HomeSlider;
		$image      = $request->get('photo');

		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$homeSlider->photo = $name;

		}


		$image_ar = $request->get('photo_ar');
		if (explode(':', $image_ar)[0] == 'data') {
			$name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

			\Image::make($image_ar)->save(public_path('images/') . $name);
			$homeSlider->photo_ar = $name;

		}
		if (isset($input['for_mobile'])) {
			$homeSlider->for_mobile = $input['for_mobile'];
		}

		if (isset($input['title'])) {
			$homeSlider->title = $input['title'];
		}

		if (isset($input['title_ar'])) {
			$homeSlider->title_ar = $input['title_ar'];
		}

		$homeSlider->save();

		return response()->json($homeSlider)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => HomeSlider::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$homeSlider = HomeSlider::find($id);

		if (is_null($homeSlider)) {
			return $this->sendError('HomeSlider not found.');
		}
		if ($homeSlider->photo) {
			$homeSlider->photo = url('/images/' . $homeSlider->photo);
		}

		if ($homeSlider->photo_ar) {
			$homeSlider->photo_ar = url('/images/' . $homeSlider->photo_ar);
		}

		return response()->json($homeSlider)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => HomeSlider::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, HomeSlider $homeSlider) {
		$input     = $request->all();
		$validator = Validator::make($input, [
			'title' => 'required',
		]);

		$homeSlider = HomeSlider::find($input['id']);

		if (is_null($homeSlider)) {
			return $this->sendError('HomeSlider not found.');
		}

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}
		$image = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$homeSlider->photo = $name;

		}

		$image_ar = $request->get('photo_ar');
		if (explode(':', $image_ar)[0] == 'data') {
			$name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

			\Image::make($image_ar)->save(public_path('images/') . $name);
			$homeSlider->photo_ar = $name;

		}
		if (isset($input['title'])) {
			$homeSlider->title = $input['title'];
		}

		if (isset($input['title_ar'])) {
			$homeSlider->title_ar = $input['title_ar'];
		}

		if (isset($input['for_mobile'])) {
			$homeSlider->for_mobile = $input['for_mobile'];
		}

		$homeSlider->save();

		return response()->json($homeSlider)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => 15,
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		$homeSlider = HomeSlider::find($id);
		$homeSlider->delete();

		return response()->json($homeSlider)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => HomeSlider::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}