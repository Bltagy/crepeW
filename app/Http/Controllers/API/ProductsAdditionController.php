<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use App\Product;
use App\ProductsAdditionCategory;
use App\ProductsAddition;
use App\ProductsAdditionsRelations;
use Illuminate\Http\Request;
use Validator;
class ProductsAdditionController extends BaseController {
	use LangData;
	

	public function productAdditions(Request $request, $id) {
		$lang  = ($request->header('X-lang')) ?? 'en';
		$input = $request->all();

		$product = Product::find($id);
		if (!$product) {
			return $this->sendError('Product doesn\'t exists.', '', 401);
		}


		$additionsPP   = [];
		$addition_cats = $product->addionCats;
		$addition_cats = $this->toLang($lang, $addition_cats);
 		$ff = $this;
		$addition_cats = $addition_cats->map(function ($item, $key) use ($ff , $lang) {
			$AddCat = $item['productsAdditionCats'];
			$AddCat = $ff->toLang($lang, $AddCat, true);
			$item = $AddCat;
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/additions/' . $item['photo']);
			}
			return $item;
		});
		// dd($addition_cats);die;
		foreach ($addition_cats as $key => $value) {

			$items          = [];
			$product_addion = ProductsAddition::where('addition_category_id', $value->id)->get();
			// dd($product_addion);die;

			foreach ($product_addion as $add) {
					$add = $this->toLang($lang, $add, true);
					if (!empty($add->photo)) {
						$add->photo = url('/images/additions/' . $add->photo);
					}
					$items[] = $add;
			}
			$value->items  = $items;

			$additionsPP[] = $value;

		}
	
		$product->additions = $additionsPP;
		$product->unsetRelation('addionCats');
		

		return response()->json($additionsPP)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAddition::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function index(Request $request) {
		$input = $request->all();

		$skip   = ($input['_start']) ?? 0;
		$sort   = ($input['_sort']) ?? 'id';
		$order  = ($input['_order']) ?? 'ASC';
		$end  = ($input['_end']) ?? 10;
		$lang   = ($request->header('X-lang')) ?? 'en';
		$web    = ($request->header('client-type')) ?? false;
		$id_like      = ($input['id_like']) ?? false;
		$mobile = ($request->header('x-Device')) ? false : true;
		// echo "string";
		// echo "string";
		// print_r($input['perPage']);die;
		$products = ProductsAddition::limit(($end - $skip))->skip($skip)->orderBy($sort, $order);

		$products = $products->get();
		if ($id_like) {
			$ids      = explode('|', $id_like);
			$products = ProductsAddition::whereIn('id', $ids)->get();
		}
		$products = $products->map(function ($item, $key) use ($mobile) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/additions/' . $item['photo']);
			}
			return $item;

		});
		return response()->json($products)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAddition::count(),
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

		$productAddition = new ProductsAddition;
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
				$addions                       = new ProductsAdditionsRelations;
				$addions->products_addition_id = $productAddition->id;
				$addions->product_id           = $product;
				$addions->save();
			}

		}

		return response()->json($productAddition)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAddition::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function show($id) {
		$productAddition = ProductsAddition::find($id);

		if (is_null($productAddition)) {
			return $this->sendError('Product not found.');
		}
		if ($productAddition->photo) {
			$productAddition->photo = url('/images/additions/' . $productAddition->photo);
		}

		$productAddition->product_id = $productAddition->products->map(function ($item, $key) {
			return $item['product_id'];
		});

		return response()->json($productAddition)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAddition::count(),
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

		$productAddition = ProductsAddition::find($id);
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
		if (isset($input['product_id']) && !empty($input['product_id'])) {
			$addions    = ProductsAdditionsRelations::where('products_addition_id', $productAddition->id)->pluck('product_id')->toArray();
			$should_add = array_diff($input['product_id'], $addions);
			if (!empty($should_add)) {
				foreach ($should_add as $add) {
					$addionsB                       = new ProductsAdditionsRelations;
					$addionsB->products_addition_id = $productAddition->id;
					$addionsB->product_id           = $add;
					$addionsB->save();
				}
			}
			$should_delete = array_diff($addions, $input['product_id']);
			if (!empty($should_delete)) {
				foreach ($should_delete as $del) {
					$productA = ProductsAdditionsRelations::where('products_addition_id', $productAddition->id)
						->where('product_id', $del)->first()->delete();
				}
			}

		}

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
		$product = ProductsAddition::find($id);
		$product->delete();

		return response()->json($product)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ProductsAddition::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}