<?php

namespace App\Http\Controllers\API;

use App\HomeSlider;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Validator;

class HomeSliderPublicController extends BaseController {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$input = $request->all();

		$skip  = ($input['_start']) ?? 0;
		$sort  = ($input['_sort']) ?? 'id';
		$order = ($input['_order']) ?? 'ASC';

		$homeSliders = HomeSlider::select('id', 'title', 'sub_title', 'photo')->skip($skip)->take(10)->orderBy($sort, $order)->get();
		foreach ($homeSliders as $key => &$value) {
			$value->photo = 'http://app.crepe-waffle.com/images/'.$value->photo;
		}

		return response()->json($homeSliders)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => HomeSlider::count(),
			'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

}