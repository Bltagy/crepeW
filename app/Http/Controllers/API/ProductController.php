<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use App\Product;
use App\ProductCategory;
use App\ProductsAddition;
use App\ProductsAdditionsRelations;
use App\ProductsRate;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Image;
use Validator;

class ProductController extends BaseController
{
    use LangData;

    /**
     * @OA\Get(
     *   path="/products",
     *   tags={"Products"},
     *   summary="Get and store product images",
     *   operationId="ListProducts",
     *   @OA\Parameter(
     *     name="category_id",
     *     description="Category ID",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="user_id",
     *     description="The user ID if available to return if the user has rated the product or not",
     *     in="query",
     *     required=false,
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
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $input = $request->all();

        $skip = ($input['_start']) ?? 0;
        $sort = ($input['_sort']) ?? 'id';
        $order = ($input['_order']) ?? 'ASC';
        $q = ($input['q']) ?? false;
        $price = ($input['price']) ?? false;
        $id_like = ($input['id_like']) ?? false;
        $end = ($input['_end']) ?? 10;
        $product_id = ($input['product_id']) ?? false;
        $product_idss = ($input['product_idss']) ?? false;
        $lang = ($request->header('X-lang')) ?? 'en';
        $web = ($request->header('client-type')) ?? false;
        $mobile = ($request->header('x-Device')) ? false : true;

        if (isset($input['id'])) {
            $uri = $request->getRequestUri();
            $uri = str_replace('/api/products?id=', '', $uri);
            $uri = str_replace('&id=', ',', $uri);
            $idsARRAY = explode(',', $uri);
//            $idsARRAY = array_map('intval', $idsARRAY);
        }
        if ($web) {
            if ( isset($input['id']) && !empty($idsARRAY) ){

                $products = Product::whereIn('id', $idsARRAY);
//                dd($idsARRAY);
            }else{
                $products = Product::limit(($end - $skip))->skip($skip)->orderBy($sort, $order);
            }
        } else {
            $products = Product::orderBy('sort', 'ASC');
        }


        if (!empty($input['category_id'])) {
            $products = $products->where('product_category_id', $input['category_id']);

        }
        if ($price) {
            $products = $products->where('medium_size', 'like', '%' . $price . '%');
        }

        if ($q) {
            $term = strtolower($q);
            $products = $products->orWhereRaw('lower(name) like (?)', ["%{$term}%"])
                ->orWhereRaw('lower(name_ar) like (?)', ["%{$term}%"])
                ->orWhereRaw('lower(description) like (?)', ["%{$term}%"])
                ->orWhereRaw('lower(description_ar) like (?)', ["%{$term}%"])
                ->orWhereRaw('lower(id) like (?)', ["%{$term}%"]);
        }

        if ($id_like) {
            $ids = [$id_like];
            if (strpos($id_like, '|') !== false) {
                $ids = explode('|', $id_like);
            }
            if (strpos($id_like, ',') !== false) {
                $ids = explode(',', $id_like);
            }

            $products = Product::whereIn('id', $ids)->get();
        } elseif ($product_id) {
            $products = Product::where('id', $product_id)->get();
        } elseif ($product_idss) {
            $query = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($query as $param) {
                list($name, $value) = explode('=', $param, 2);
                $params[urldecode($name)][] = urldecode($value);
            }

            $products = Product::whereIn('id', $params['product_idss'])->get();
        } else {
            if ($web) {
                $products = $products->get();
            } else {
                $products = $products->get();
            }
        }

        if (!empty($input['user_id'])) {
            foreach ($products as $key => &$value) {
                $value->is_rated = (ProductsRate::where('product_id', $value)->where('user_id', $input['user_id'])->count()) ?? 0;
            }
        }

        if ($lang == 'ar') {
            $products = $products->map(function ($item, $key) use ($mobile) {

                if ($mobile) {
                    if (!empty($item['mobile_photo_ar'])) {
                        $item['photo'] = url('/images/' . $item['mobile_photo_ar']);
                    }
                } else {
                    if (!empty($item['photo_ar'])) {
                        $item['photo'] = url('/images/' . $item['photo_ar']);
                    }
                }
                $item['name'] = $item['name_ar'];
                $item['description'] = $item['description_ar'];
                unset($item['name_ar']);
                unset($item['mobile_photo_ar']);
                unset($item['mobile_photo']);
                unset($item['photo_ar']);
                unset($item['description_ar']);
                unset($item['pos_id']);
                return $item;

            });
        } else {
            $products = $products->map(function ($item, $key) use ($mobile) {

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
                unset($item['pos_id']);

                return $item;

            });
        }
        $products = $products->map(function ($item, $key) use ($mobile) {
            $item['additions'] = [['id' => 1, 'name' => 'اضافة لحوم', 'items' => [['id' => 2, 'name' => 'اضافة هوت دوج', 'price' => '20'], ['id' => 3, 'name' => 'اضافة هوت دوج', 'price' => '20']]],
                ['id' => 11, 'name' => 'اضافة جبن', 'items' => [['id' => 22, 'name' => 'اضافة جبنه كيرى', 'price' => '20'], ['id' => 33, 'name' => 'اضافة جبنه موتزاريلا', 'price' => '20']]],
            ];
            return $item;
        });

        return response()->json($products)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    public function indexAddition(Request $request)
    {
        $input = $request->all();

        $skip = ($input['_start']) ?? 0;
        $sort = ($input['_sort']) ?? 'id';
        $order = ($input['_order']) ?? 'ASC';
        $q = ($input['q']) ?? false;
        $price = ($input['price']) ?? false;
        $id_like = ($input['id_like']) ?? false;
        $end = ($input['_end']) ?? false;
        $product_id = ($input['product_id']) ?? false;
        $product_idss = ($input['product_idss']) ?? false;

        if (empty($product_idss)) {
            return response()->json([])->withHeaders([
                'Content-Range' => 'products 0-1/1',
                'X-Total-Count' => Product::count(),
                //'Cache-Control'                 => 'max-age=604800, public',
                'Access-Control-Expose-Headers' => 'X-Total-Count',
            ]);

        }
        if ($web) {
            $products = Product::skip($skip)->take(10)->orderBy($sort, $order);
        } elseif ($web && $end == 25) {
            $products = Product::skip($skip)->orderBy($sort, $order);
        } else {
            $products = Product::orderBy('sort', 'ASC');
        }

        $products = $products->orderBy($sort, $order);

        if (!empty($input['category_id'])) {
            $products = Product::where('product_category_id', $input['category_id']);

        }
        if ($price) {
            $products = $products->Where('small_size', 'like', '%' . $price . '%')
                ->OrWhere('medium_size', 'like', '%' . $price . '%')
                ->Where('large_size', 'like', '%' . $price . '%');
        }

        if ($q) {
            $products = $products->Where('name', 'like', '%' . $q . '%')
                ->OrWhere('name_ar', 'like', '%' . $q . '%')
                ->OrWhere('description', 'like', '%' . $q . '%')
                ->OrWhere('description_ar', 'like', '%' . $q . '%')
                ->OrWhere('id', $q);
        }

        if ($id_like) {
            $ids = explode(',', $id_like);
            $products = Product::whereIn('id', $ids)->get();
        } elseif ($product_id) {
            $products = Product::where('id', $product_id)->get();
        } elseif ($product_idss) {
            $query = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($query as $param) {
                list($name, $value) = explode('=', $param, 2);
                $params[urldecode($name)][] = urldecode($value);
            }

            $products = Product::whereIn('id', $params['product_idss'])->get();
        } else {
            $products = $products->get();
        }

        if (!empty($input['user_id'])) {
            foreach ($products as $key => &$value) {
                $value->is_rated = (ProductsRate::where('product_id', $value)->where('user_id', $input['user_id'])->count()) ?? 0;
            }
        }

        if ($lang == 'ar') {
            $products = $products->map(function ($item, $key) {
                $item['photo'] = url('/images/' . $item['photo_ar']);
                $item['name'] = $item['name_ar'];
                $item['description'] = $item['description_ar'];
                unset($item['name_ar']);
                unset($item['photo_ar']);
                unset($item['description_ar']);
                return $item;

            });
        } else {
            $products = $products->map(function ($item, $key) {
                $item['photo'] = url('/images/' . $item['photo']);
                unset($item['name_ar']);
                unset($item['photo_ar']);
                unset($item['description_ar']);
                return $item;

            });
        }
        if ($request->header('client-type') != 'web') {

            $products = $products->map(function ($item, $key) use ($request) {

                $item['addtions'] = ProductsAdditionsRelations::where('product_id', $item['id'])->with('productsAddition')->get();

                $item['addtions'] = $item['addtions']->map(function ($item2, $key) use ($request) {
                    unset($item2['id']);
                    unset($item2['products_addition_id']);
                    unset($item2['product_id']);

                    if ($lang == 'ar') {
                        $item2->productsAddition['name'] = $item2->productsAddition['name_ar'];
                        $item2->productsAddition['description'] = $item2->productsAddition['description_ar'];
                        unset($item2->productsAddition['name_ar']);
                        unset($item2->productsAddition['description_ar']);
                    } else {
                        unset($item2->productsAddition['name_ar']);
                        unset($item2->productsAddition['description_ar']);
                    }
                    if (!empty($item2->productsAddition['photo'])) {
                        $item2->productsAddition['photo'] = url('/images/' . $item2->productsAddition['photo']);
                    }
                    $item2 = $item2->productsAddition;
                    unset($item2->productsAddition);
                    return $item2;
                });

                return $item;

            });
        }

        return response()->json($products)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    /**
     * @OA\Get(
     *   path="/productsByCategories",
     *   tags={"Products"},
     *   summary="Get products categorized ",
     *   operationId="productsByCategories",
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
     * @return Response
     */
    public function productsByCategories(Request $request)
    {
        $input = $request->all();

        $skip = ($input['_start']) ?? 0;
        $sort = ($input['_sort']) ?? 'id';
        $order = ($input['_order']) ?? 'ASC';
        $lang = ($request->header('X-lang')) ?? 'en';

        $products = [];

        $cats = ProductCategory::orderBy('sort', 'ASC')->get();
        $cats = $this->toLang($lang, $cats);
        foreach ($cats as $key => $cat) {
            $c_products = Product::orderBy('sort', 'ASC')->where('product_category_id', $cat->id)->get();
            $c_products = $this->toLang($lang, $c_products);
            $c_products = $c_products->map(function ($item, $key) {
                if (!empty($item['photo'])) {
                    $item['photo'] = url('/images/' . $item['photo']);
                }
                if (!empty($item['mobile_photo'])) {
                    $item['mobile_photo'] = url('/images/' . $item['mobile_photo']);
                }
                return $item;

            });
            $products[] = ['category_name' => $cat->name,
                'category_id' => $cat->id,
                'category_products' => $c_products,
            ];
        }

        return response()->json($products)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $product = new Product;
        $image = $request->get('photo');
        if (explode(':', $image)[0] == 'data') {
            $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

            Image::make($image)->save(public_path('images/') . $name);
            $product->photo = $name;

        }

        $image_ar = $request->get('photo_ar');
        if (explode(':', $image_ar)[0] == 'data') {
            $name = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

            Image::make($image_ar)->save(public_path('images/') . $name);
            $product->photo_ar = $name;

        }

        $mobile_image_ar = $request->get('mobile_photo_ar');
        if (explode(':', $mobile_image_ar)[0] == 'data') {
            $name = time() . '_ar_mobile.' . explode('/', explode(':', substr($mobile_image_ar, 0, strpos($mobile_image_ar, ';')))[1])[1];

            Image::make($mobile_image_ar)->save(public_path('images/') . $name);
            $product->mobile_photo_ar = $name;

        }

        $mobile_image = $request->get('mobile_photo');
        if (explode(':', $mobile_image)[0] == 'data') {
            $name = time() . '_mobile.' . explode('/', explode(':', substr($mobile_image, 0, strpos($mobile_image, ';')))[1])[1];

            Image::make($mobile_image)->save(public_path('images/') . $name);
            $product->mobile_photo = $name;

        }

        if (isset($input['in_stock'])) {
            $product->in_stock = $input['in_stock'];
        }

        if (isset($input['featured'])) {
            $product->featured = $input['featured'];
        }

        if (isset($input['product_category_id'])) {
            $product->product_category_id = $input['product_category_id'];
        }

        if (isset($input['small_size'])) {
            $product->small_size = $input['small_size'];
        }
        if (isset($input['medium_size'])) {
            $product->medium_size = $input['medium_size'];
        }
        if (isset($input['large_size'])) {
            $product->large_size = $input['large_size'];
        }

        if (isset($input['name'])) {
            $product->name = $input['name'];
        }

        if (isset($input['name_ar'])) {
            $product->name_ar = $input['name_ar'];
        }

        if (isset($input['description'])) {
            $product->description = $input['description'];
        }

        if (isset($input['description_ar'])) {
            $product->description_ar = $input['description_ar'];
        }

        $product->save();
        return response()->json($product)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function submitRate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'product_id' => 'required',
            'user_id' => 'required',
            'rate' => 'required|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($input['user_id']);

        if (!$user) {
            return $this->sendError('User doesn\'t exists.', '', 404);
        }

        $product = Product::find($input['product_id']);

        if (!$product) {
            return $this->sendError('Product doesn\'t exists.', '', 404);
        }

        $product_rate = New ProductsRate;
        $product_rate->product_id = $input['product_id'];
        $product_rate->user_id = $input['user_id'];
        $product_rate->rate = $input['rate'];
        $product_rate->save();

        $rates = ProductsRate::where('product_id', $input['product_id'])->avg('rate');
        $product->rate = round($rates, 1);
        $product->save();

        return response()->json($product)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    /**
     * @OA\Get(
     *   path="/products/{id}",
     *   tags={"Products"},
     *   summary="Show one",
     *   @OA\Parameter(
     *     name="id",
     *     description="Product ID",
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
     * @param int $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $web = ($request->header('client-type')) ?? false;
        $lang = ($request->header('X-lang')) ?? 'en';
        $product = Product::find($id);
        // dd($product);die;

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
        if ($product->photo) {
            $product->photo = url('/images/' . $product->photo);
        }
        if ($product->photo_ar) {
            $product->photo_ar = url('/images/' . $product->photo_ar);
        }

        if ($product->mobile_photo_ar) {
            $product->mobile_photo_ar = url('/images/' . $product->mobile_photo_ar);
        }

        if ($product->mobile_photo) {
            $product->mobile_photo = url('/images/' . $product->mobile_photo);
        }

        $additionsPP = [];
        $addition_cats = $product->addionCats;
        $addition_cats = $this->toLang($lang, $addition_cats);
        $ff = $this;
        $addition_cats = $addition_cats->map(function ($item, $key) use ($ff, $lang) {
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

            $items = [];
            $product_addion = ProductsAddition::where('addition_category_id', $value->id)->get();
            // dd($product_addion);die;

            foreach ($product_addion as $add) {
                $add = $this->toLang($lang, $add, true);
                if (!empty($add->photo)) {
                    $add->photo = url('/images/additions/' . $add->photo);
                }
                $items[] = $add;
            }
            $value->items = $items;

            $additionsPP[] = $value;

        }
        if (!$web) {
            $product = $this->toLang($lang, $product, true);
        }

        $product->additions = $additionsPP;
        $product->unsetRelation('addionCats');
        return response()->json($product)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $product = Product::find($input['id']);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        if (isset($input['remove_photo_ar']) && $input['remove_photo_ar'] == 1) {
            $product->photo_ar = null;
        }

        if (isset($input['remove_photo']) && $input['remove_photo'] == 1) {
            $product->photo = null;
        }

        if (isset($input['remove_mobile_photo_ar']) && $input['remove_mobile_photo_ar'] == 1) {
            $product->mobile_photo_ar = null;
        }

        if (isset($input['remove_mobile_photo']) && $input['remove_mobile_photo'] == 1) {
            $product->mobile_photo = null;
        }


        $image = $request->get('photo');
        if (explode(':', $image)[0] == 'data') {
            $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

            Image::make($image)->save(public_path('images/') . $name);
            $product->photo = $name;

        }

        $image_ar = $request->get('photo_ar');
        if (explode(':', $image_ar)[0] == 'data') {
            $name_ar = time() . '_ar.' . explode('/', explode(':', substr($image_ar, 0, strpos($image_ar, ';')))[1])[1];

            Image::make($image_ar)->save(public_path('images/') . $name_ar);
            $product->photo_ar = $name_ar;

        }

        $mobile_image_ar = $request->get('mobile_photo_ar');
        if (explode(':', $mobile_image_ar)[0] == 'data') {
            $name = time() . '_ar_mobile.' . explode('/', explode(':', substr($mobile_image_ar, 0, strpos($mobile_image_ar, ';')))[1])[1];

            Image::make($mobile_image_ar)->save(public_path('images/') . $name);
            $product->mobile_photo_ar = $name;

        }

        $mobile_image = $request->get('mobile_photo');
        if (explode(':', $mobile_image)[0] == 'data') {
            $name = time() . '_mobile.' . explode('/', explode(':', substr($mobile_image, 0, strpos($mobile_image, ';')))[1])[1];

            Image::make($mobile_image)->save(public_path('images/') . $name);
            $product->mobile_photo = $name;

        }

        if (isset($input['in_stock'])) {
            $product->in_stock = $input['in_stock'];
        }

        if (isset($input['featured'])) {
            $product->featured = $input['featured'];
        }

        if (isset($input['product_category_id'])) {
            $product->product_category_id = $input['product_category_id'];
        }

        if (isset($input['small_size'])) {
            $product->small_size = $input['small_size'];
        }
        if (isset($input['medium_size'])) {
            $product->medium_size = $input['medium_size'];
        }
        if (isset($input['large_size'])) {
            $product->large_size = $input['large_size'];
        }

        if (isset($input['name'])) {
            $product->name = $input['name'];
        }

        if (isset($input['name_ar'])) {
            $product->name_ar = $input['name_ar'];
        }

        if (isset($input['description'])) {
            $product->description = $input['description'];
        }

        if (isset($input['description_ar'])) {
            $product->description_ar = $input['description_ar'];
        }

        $product->save();

        return response()->json($product)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => 15,
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }


    public function delProductPhoto(Request $request, $id)
    {
        $input = $request->all();

        $product = Product::find($id);
        $type = $input['type'];
        $product->$type = null;
        $product->save();

        return response()->json($product)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => 15,
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();

        return response()->json($product)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Product::count(),
            //'Cache-Control'                 => 'max-age=604800, public',
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }
}
