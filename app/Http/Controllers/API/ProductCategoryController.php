<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Product;
use App\ProductCategory;
use Illuminate\Http\Request;
use Validator;

class ProductCategoryController extends BaseController {

	/**
	 * @OA\Get(
	 *   path="/productCategory",
	 *   tags={"ProductCategory"},
	 *   summary="Get and store productCategory images",
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

		$skip    = ($input['_start']) ?? 0;
		$sort    = ($input['_sort']) ?? 'id';
		$order   = ($input['_order']) ?? 'ASC';
		$perPage = ($input['_end']) ?? 10;
		$lang    = ($request->header('X-lang')) ?? 'en';
		$web     = ($request->header('client-type')) ?? false;

		if ($web) {
			$productCategorys = ProductCategory::skip($skip)->take($perPage)->orderBy($sort, $order)->get();
		} else {
			$productCategorys = ProductCategory::orderBy('sort', 'ASC')->get();
		}

		if ($lang == 'ar') {
			$productCategorys = $productCategorys->map(function ($item, $key) {
				if (!empty($item['photo_ar'])) {
					$item['photo'] = url('/images/categories/' . $item['photo_ar']);
				}
				$item['name'] = $item['name_ar'];
				unset($item['name_ar']);
				unset($item['photo_ar']);
				return $item;

			});
		} else {
			$productCategorys = $productCategorys->map(function ($item, $key) {
				if (!empty($item['photo'])) {
					$item['photo'] = url('/images/categories/' . $item['photo']);
				}
				unset($item['name_ar']);
				unset($item['photo_ar']);
				return $item;

			});
		}

		return response()->json($productCategorys)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => ProductCategory::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function prodcutsNcategories(Request $request) {
		$data             = [];
		$productCategorys = ProductCategory::orderBy('sort','ASC')->get();
		foreach ($productCategorys as $key => $cat) {
			$data['lanes'][$key] = [
				"id"    => "cat" . $cat->id,
				"title" => $cat->name,
				"label" => $cat->product->count() . " Products",
			];
			foreach ($cat->product as $product) {
				$data['lanes'][$key]['cards'][] = [
					"id"    => "pro" . $product->id,
					"title" => $product->name,
				];
			}
		}

		return response()->json($data)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => ProductCategory::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function saveSort(Request $request) {
		$input = $request->all();
		if (empty($input['data']['lanes'])) {
			return $this->sendError('Empty data.');
		}
		foreach ($input['data']['lanes'] as $CatsKey => $cats) {
			$catId   = preg_replace("/[^0-9]/", "", $cats['id']);
			$c       = ProductCategory::find($catId);
			$c->sort = $CatsKey;
			$c->save();
			if (empty($cats['cards'])) {
				continue;
			}

			foreach ($cats['cards'] as $proKey => $pro) {
				$proId   = preg_replace("/[^0-9]/", "", $pro['id']);
				$p       = Product::find($proId);
				$p->sort = $CatsKey . $proKey;
				$p->save();
			}
		}

		$data             = [];
		$productCategorys = ProductCategory::orderBy('sort','ASC')->get();
		foreach ($productCategorys as $key => $cat) {
			$data['lanes'][$key] = [
				"id"    => "cat" . $cat->id,
				"title" => $cat->name,
				"label" => $cat->product->count() . " Products",
			];
			foreach ($cat->product as $product) {
				$data['lanes'][$key]['cards'][] = [
					"id"    => "pro" . $product->id,
					"title" => $product->name,
				];
			}
		}

		return response()->json($data)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => ProductCategory::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
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

		$productCategory = new ProductCategory;

		$image = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('/images/categories/') . $name);
			$productCategory->photo = $name;

		}

		$image_ar = $request->get('photo_ar');
		if (explode(':', $image_ar)[0] == 'data') {
			$name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

			\Image::make($image_ar)->save(public_path('/images/categories/') . $name);
			$productCategory->photo_ar = $name;

		}
		if (isset($input['name'])) {
			$productCategory->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$productCategory->name_ar = $input['name_ar'];
		}
		$productCategory->save();
		return response()->json($productCategory)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => ProductCategory::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * @OA\Get(
	 *   path="/productCategory/{id}",
	 *   tags={"ProductCategory"},
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
	public function show(Request $request, $id) {
		$productCategory = ProductCategory::find($id);
		$lang            = ($request->header('X-lang')) ?? 'en';
		$web             = ($request->header('client-type')) ?? false;

		if (is_null($productCategory)) {
			return $this->sendError('ProductCategory not found.');
		}

		if (!$web) {
			if ($lang == 'ar') {
				if (!empty($productCategory->photo_ar)) {
					$productCategory->photo = url('/images/categories/' . $productCategory->photo_ar);
				} else {
					$productCategory->photo = url('/images/categories/' . $productCategory->photo);
				}
				$productCategory->name = $productCategory->name_ar;
				unset($productCategory->name_ar);
				unset($productCategory->photo_ar);

			} else {
				if (!empty($productCategory->photo)) {
					$productCategory->photo = url('/images/categories/' . $productCategory->photo);
				}
				unset($productCategory->name_ar);
				unset($productCategory->photo_ar);
			}
		} else {
			if (!empty($productCategory->photo)) {
				$productCategory->photo = url('/images/categories/' . $productCategory->photo);
			}
			if (!empty($productCategory->photo_ar)) {
				$productCategory->photo_ar = url('/images/categories/' . $productCategory->photo_ar);
			}
		}

		return response()->json($productCategory)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => ProductCategory::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
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
	public function update(Request $request, ProductCategory $productCategory) {
		$input = $request->all();

		$productCategory = ProductCategory::find($input['id']);
		$image           = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/categories/') . $name);
			$productCategory->photo = $name;

		}

		$image_ar = $request->get('photo_ar');
		if (explode(':', $image_ar)[0] == 'data') {
			$name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

			\Image::make($image_ar)->save(public_path('images/categories/') . $name);
			$productCategory->photo_ar = $name;

		}
		if (isset($input['name'])) {
			$productCategory->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$productCategory->name_ar = $input['name_ar'];
		}
		$productCategory->save();

		return response()->json($productCategory)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => 15,
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(ProductCategory $productCategory) {
		$productCategory->delete();

		return response()->json($productCategory)->withHeaders([
			'Content-Range'                 => 'productCategorys 0-1/1',
			'X-Total-Count'                 => ProductCategory::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}