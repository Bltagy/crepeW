<?php

namespace App\Http\Controllers\API;

use App\Area;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use Illuminate\Http\Request;
use Validator;

class AreaController extends BaseController {
	use LangData;
	/**
	 * @OA\Get(
	 *   path="/areas",
	 *   tags={"Content"},
	 *   summary="Retrieve the list of areas with its fees each.",
	 *   operationId="areas",
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
	public function index(Request $request) {
		$lang = ($request->header('X-lang')) ?? 'en';
		$input = $request->all();

		$skip    = ($input['_start']) ?? 0;
		$sort    = ($input['_sort']) ?? 'id';
		$order   = ($input['_order']) ?? 'ASC';
		$end = ($input['_end']) ?? 10;
		$web     = ($request->header('client-type')) ?? false;
		$q       = ($input['q']) ?? false;

		$response = [];
		if ($web) {
			$area = Area::select('id', 'name', 'name_ar', 'fees', 'serve_until', 'is_24'


            )->limit(($end - $skip))->skip($skip)->orderBy($sort, $order);
		} else {
			$area = Area::select('id', 'name', 'name_ar', 'fees', 'serve_until');
		}

		if ($q) {
			$area = $area->Where('name', 'like', '%' . $q . '%')
				->OrWhere('id', $q);
		}

		$area = $area->get();
		$area = $area->map(function ($item, $key) {
			if (!empty($item['serve_until'])) {
				$item['serve_until'] = \Carbon\Carbon::parse($item['serve_until'])->setTimezone('EET')->format('h:i a');
			}
			return $item;

		});
		$area = $this->toLang($lang, $area);
		if ($request->header('Client-Type') == 'web') {
			return response()->json($area)->withHeaders([
				'Content-Range'                 => 'area 0-1/1',
				'X-Total-Count'                 => Area::count(),
				'Access-Control-Expose-Headers' => 'X-Total-Count',
			]);
		}

		return response()->json($area)->withHeaders([
			'Content-Range'                 => 'area 0-1/1',
			'X-Total-Count'                 => Area::count(),
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
			'name' => 'required',
			'fees' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$area = new Area;

		if (isset($input['fees'])) {
			$area->fees = $input['fees'];
		}

		if (isset($input['name'])) {
			$area->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$area->name_ar = $input['name_ar'];
		}

		if (isset($input['serve_until'])) {
			$area->serve_until = \Carbon\Carbon::parse($input['serve_until'])->setTimezone('EET')->format('h:i');
			$area->is_24 = 0;
		}

		$area->save();

		return response()->json($area)->withHeaders([
			'Content-Range'                 => 'area 0-1/1',
			'X-Total-Count'                 => Area::count(),
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
		$area = Area::find($id);

		if (is_null($area)) {
			return $this->sendError('Area not found.');
		}

		if (!empty($area->serve_until)) {
			$time              = \Carbon\Carbon::parse($area->serve_until)->format('Y-m-d    H:i:s');
			$area->serve_until = str_replace('    ', 'T', $time);
		}
		$area->is_24 = "$area->is_24";

		return response()->json($area)->withHeaders([
			'Content-Range'                 => 'area 0-1/1',
			'X-Total-Count'                 => Area::count(),
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
	public function update(Request $request, Area $area) {
		$input = $request->all();

		$area = Area::find($input['id']);

		if (isset($input['serve_until'])) {
			$area->serve_until = \Carbon\Carbon::parse($input['serve_until'])->setTimezone('EET')->toDateTimeString();
		}
		if (isset($input['is_24'])) {
			$area->is_24 = $input['is_24'];
		}

		$area->save();

		return response()->json($area)->withHeaders([
			'Content-Range'                 => 'area 0-1/1',
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
		$area = Area::find($id);
		$area->delete();

		return response()->json($area)->withHeaders([
			'Content-Range'                 => 'area 0-1/1',
			'X-Total-Count'                 => Area::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}
