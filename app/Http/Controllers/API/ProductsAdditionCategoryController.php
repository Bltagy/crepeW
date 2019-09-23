<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use App\Product;
use App\ProductsAddition;
use App\ProductsAdditionCategory;
use App\ProductsAdditionCategorysRelations;
use Illuminate\Http\Request;
use Validator;

class ProductsAdditionCategoryController extends BaseController {
	use LangData;

	/**
	 * @OA\Get(
	 *   path="/productsAddition/{product_id}",
	 *   tags={"Products"},
	 *   summary="Get product additions",
	 *   operationId="productsAddition",
	 *    @OA\Response(response="200",
	 *     description="",
	 *   )
	 * )
	 */

	public function productAdditions(Request $request, $id) {
		$lang  = ($request->header('X-lang')) ?? 'en';
		$input = $request->all();

		$product = Product::find($id);
		if (!$product) {
			return $this->sendError('Product doesn\'t exists.', '', 401);
		}
		$addions = [];
		$ids     = $product->addions->pluck('id')->toArray();
		foreach ($product->addions as $key => $value) {
			if (!empty($value->category->id)) {

				$addions[$value->category->id] = [
					'id'   => $value->category->id,
					'name' => $this->name($lang, $value->category),
				];
				foreach ($value->category->additions as $key2 => $value2) {
					if (in_array($value2->id, $ids)) {
						$addions[$value->category->id]['items'][] = [
							'id'    => $value2->id,
							'name'  => $this->name($lang, $value2),
							'price' => $value2->price,
							'photo' => $value2->phoio,
						];
					}
				}

			}
		}

		$addions = array_values($addions);

		// /**
		//  * Temp
		//  */

		// $addions = [['id' => 1, 'name' => 'اضافة لحوم', 'items' => [['id' => 2, 'name' => 'اضافة هوت دوج', 'price' => '20'], ['id' => 3, 'name' => 'اضافة هوت دوج', 'price' => '20']]],
		// 	['id' => 11, 'name' => 'اضافة جبن', 'items' => [['id' => 22, 'name' => 'اضافة جبنه كيرى', 'price' => '20'], ['id' => 33, 'name' => 'اضافة جبنه موتزاريلا', 'price' => '20']]],
		// ];

		return response()->json($addions)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAdditionCategory::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function index(Request $request) {
		$input = $request->all();

		$skip   = ($input['_start']) ?? 0;
		$sort   = ($input['_sort']) ?? 'id';
		$order  = ($input['_order']) ?? 'ASC';
		$take   = ($input['_end']) ?? 10;
		$lang   = ($request->header('X-lang')) ?? 'en';
		$web    = ($request->header('client-type')) ?? false;
		$mobile = ($request->header('x-Device')) ? false : true;
		// echo "string";
		// echo "string";
		// print_r($input['perPage']);die;
		$products = ProductsAdditionCategory::skip($skip)->take($take)->orderBy($sort, $order);

		$products = $products->get();

		$products = $products->map(function ($item, $key) use ($mobile) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/additions/' . $item['photo']);
			}
			return $item;

		});

		return response()->json($products)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAdditionCategory::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function store(Request $request) {
		$input = $request->all();

		$validator = Validator::make($input, [
			'name'  => 'required',
			'price' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$productAddition = new ProductsAdditionCategory;
		$image           = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$productAddition->photo = $name;

		}

		if (isset($input['name'])) {
			$productAddition->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$productAddition->name_ar = $input['name_ar'];
		}

		if (isset($input['description'])) {
			$productAddition->description = $input['description'];
		}

		if (isset($input['description_ar'])) {
			$productAddition->description_ar = $input['description_ar'];
		}

		if (isset($input['price'])) {
			$productAddition->price = $input['price'];
		}

		$productAddition->save();

		if (isset($input['product_id']) && !empty($input['product_id'])) {
			foreach ($input['product_id'] as $key => $product) {
				$addions                       = new ProductsAdditionCategorysRelations;
				$addions->products_addition_id = $productAddition->id;
				$addions->product_id           = $product;
				$addions->save();
			}

		}

		return response()->json($productAddition)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAdditionCategory::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function show($id) {
		$productAddition = ProductsAdditionCategory::find($id);

		if (is_null($productAddition)) {
			return $this->sendError('Product not found.');
		}
		if ($productAddition->photo) {
			$productAddition->photo = url('/images/additions/' . $productAddition->photo);
		}

		return response()->json($productAddition)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAdditionCategory::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
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
	public function update(Request $request, $id) {

		$input     = $request->all();
		$validator = Validator::make($input, [
			'name' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$productAddition = ProductsAdditionCategory::find($id);
		$image           = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/additions/') . $name);
			$productAddition->photo = $name;

		}

		if (isset($input['name'])) {
			$productAddition->name = $input['name'];
		}

		if (isset($input['name_ar'])) {
			$productAddition->name_ar = $input['name_ar'];
		}

		if (isset($input['description'])) {
			$productAddition->description = $input['description'];
		}

		if (isset($input['description_ar'])) {
			$productAddition->description_ar = $input['description_ar'];
		}

		if (isset($input['price'])) {
			$productAddition->price = $input['price'];
		}

		$productAddition->save();

		return response()->json($productAddition)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
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
	public function destroy($id) {
		$product = ProductsAdditionCategory::find($id);
		$product->delete();

		return response()->json($product)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAdditionCategory::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}