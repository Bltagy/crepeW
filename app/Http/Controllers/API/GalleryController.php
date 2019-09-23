<?php

namespace App\Http\Controllers\API;

use App\Gallery;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Validator;

class GalleryController extends BaseController {

	/**
	 * @OA\Get(
	 *   path="/gallery",
	 *   tags={"Sliders&gallery"},
	 *   summary="Get and store gallery images",
	 *   @OA\Parameter(
	 *     name="_start",
	 *     description="User name",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="_sort",
	 *     description="User email",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="_order",
	 *     description="Chosen password",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *
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
		$input = $request->all();

		$skip  = ($input['_start']) ?? 0;
		$sort  = ($input['_sort']) ?? 'id';
		$order = ($input['_order']) ?? 'ASC';
		$q     = ($input['q']) ?? false;
		$take  = ($input['_end']) ?? 10;

		$gallerys = Gallery::skip($skip)->take($take)->orderBy($sort, $order);

		if ($q) {
			$gallerys = $gallerys->Where('name', 'like', '%' . $q . '%');
		}

		$gallerys = $gallerys->get();

		return response()->json($gallerys)->withHeaders([
			'Content-Range'                 => 'gallerys 0-1/1',
			'X-Total-Count'                 => Gallery::count(),
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
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$gallery = new Gallery;
		$image   = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$gallery->photo = $name;

		}

		$image_ar = $request->get('photo_ar');
		if (explode(':', $image_ar)[0] == 'data') {
			$name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

			\Image::make($image_ar)->save(public_path('images/') . $name);
			$gallery->photo_ar = $name;

		}
		if (isset($input['name'])) {
			$gallery->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$gallery->name_ar = $input['name_ar'];
		}
		$gallery->save();
		return response()->json($gallery)->withHeaders([
			'Content-Range'                 => 'gallerys 0-1/1',
			'X-Total-Count'                 => Gallery::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * @OA\Get(
	 *   path="/gallery/{id}",
	 *   tags={"Sliders&gallery"},
	 *   summary="Show one",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User name",
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
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		$gallery = Gallery::find($id);

		if (is_null($gallery)) {
			return $this->sendError('Gallery not found.');
		}

		if ($gallery->photo) {
			$gallery->photo = url('/images/' . $gallery->photo);
		}
		if ($gallery->photo_ar) {
			$gallery->photo_ar = url('/images/' . $gallery->photo_ar);
		}

		return response()->json($gallery)->withHeaders([
			'Content-Range'                 => 'gallerys 0-1/1',
			'X-Total-Count'                 => Gallery::count(),
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
	public function update(Request $request, Gallery $gallery) {
		$input     = $request->all();
		$validator = Validator::make($input, [
			'name' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$gallery = Gallery::find($input['id']);
		$image   = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$gallery->photo = $name;

		}

		$image_ar = $request->get('photo_ar');
		if (explode(':', $image_ar)[0] == 'data') {
			$name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

			\Image::make($image_ar)->save(public_path('images/') . $name);
			$gallery->photo_ar = $name;

		}
		if (isset($input['name'])) {
			$gallery->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$gallery->name_ar = $input['name_ar'];
		}
		$gallery->save();

		return response()->json($gallery)->withHeaders([
			'Content-Range'                 => 'gallerys 0-1/1',
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
	public function destroy(Gallery $gallery) {
		$gallery->delete();

		return response()->json($gallery)->withHeaders([
			'Content-Range'                 => 'gallerys 0-1/1',
			'X-Total-Count'                 => Gallery::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}