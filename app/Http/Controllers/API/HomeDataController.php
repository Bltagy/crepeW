<?php

namespace App\Http\Controllers\API;

use App\Gallery;
use App\HomeData;
use App\HomeSlider;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use App\Order;
use App\Product;
use App\ProductCategory;
use App\ProductsRate;
use App\ServiceRate;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class HomeDataController extends BaseController {
	use LangData;
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {

		$input = $request->all();

		$only_data = ($input['data']) ?? 0;

		$user_id = ($input['user_id']) ?? 0;

		$lang = ($request->header('X-lang')) ?? 'en';

		$data = [];

		$data = HomeData::pluck('value', 'name');

		if ($lang == 'ar') {
			$data['about']    = $data['about_ar'];
			$data['contacts'] = $data['contacts_ar'];
			unset($data['about_ar']);
			unset($data['contacts_ar']);
		}

		if (!$only_data) {
			$slider = HomeSlider::select('title', 'photo', 'title_ar', 'photo_ar')->where('for_mobile','!=',1)->get();
			$slider = $this->toLang($lang, $slider);
			$slider = $slider->map(function ($item, $key) {
				if (!empty($item['photo'])) {
					$item['photo'] = url('/images/' . $item['photo']);
				}
				return $item;

			});
			$data['slider'] = $slider->toArray();

		}
		$products = Product::orderBy('sort')->where('in_stock', 1)->get();
		$products = $this->toLang($lang, $products);

		$data['products'] = $products->map(function ($item, $key) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/' . $item['photo']);
			}
			return $item;
		});
		$data['needRate'] = 0;
		if (!empty($input['user_id'])) {
			foreach ($data['products'] as $key => &$value) {
				$value->is_rated = (ProductsRate::where('product_id', $value->id)->where('user_id', $input['user_id'])->count()) ?? 0;
			}

			$latestOrder = Order::where('user_id', $input['user_id'])->latest()->first();
			if ($latestOrder) {
				$hasRate = ServiceRate::where('user_id', $input['user_id'])
					->where('order_id', $latestOrder->id)
					->count();
				if (!$hasRate) {
					$data['needRate'] = $latestOrder->id;
				}
			}
		}
		$ProductCategory       = ProductCategory::orderBy('sort', 'ASC')->get();
		$ProductCategory       = $this->toLang($lang, $ProductCategory);
		$data['products_cats'] = $ProductCategory;
		$featured              = Product::orderBy('sort', 'ASC')->where('featured', 1)->where('in_stock', 1)->take(3)->get();
		$featured              = $this->toLang($lang, $featured);

		$featured = $featured->map(function ($item, $key) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/' . $item['photo']);
			}
			return $item;
		});
		$data['featured'] = $featured->toArray();
		$gallery          = Gallery::whereNotNull('photo')->get();
		$gallery          = $this->toLang($lang, $gallery);
		$gallery          = $gallery->map(function ($item, $key) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/' . $item['photo']);
			}
			return $item;
		});
		$data['gallery'] = $gallery->toArray();
		$data['closed']  = ($data['closed'] == 1) ? true : false;
		return response()->json($data)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
			'X-Total-Count'                 => HomeData::count(),
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

		$homeData = new HomeData;
		if ($request->get('photo')) {
			$image = $request->get('photo');
			// $image = $request->image;  // your base64 encoded
			$name  = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
			$image = str_replace('data:image/png;base64,', '', $image);
			$image = str_replace('data:image/jpg;base64,', '', $image);
			$image = str_replace('data:image/jpeg;base64,', '', $image);

			\Image::make($image)->save(public_path('images/') . $name);
			$homeData->photo = $name;

		}
		$homeData->title     = $input['title'];
		$homeData->sub_title = $input['sub_title'];
		$homeData->save();
		return response()->json($homeData)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
			'X-Total-Count'                 => HomeData::count(),
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
		$homeData = HomeData::all();

		if (is_null($homeData)) {
			return $this->sendError('HomeData not found.');
		}

		return response()->json($homeData)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
			'X-Total-Count'                 => HomeData::count(),
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
	public function statistics() {

		$data          = [];
		$data['users'] = User::count();

		$data['todayOrders']   = Order::whereDate('created_at', \Carbon\Carbon::today())->count();
		$data['totalOrders']   = Order::count();
		$data['topUsers']      = User::withCount('orders')->orderBy('orders_count', 'desc')->take(5)->get();
		$data['pendingOrders'] = Order::where('order_status', '!=','Completed')->with('orderDetailes', 'user')->orderBy('id', 'desc')->take(10)->get();
		$data['latest_sync']   = \Carbon\Carbon::parse(HomeData::where('name', 'latest_sync')->first()->value)->addHours(2)->format('Y-m-d h:m A');
		$data['pendingOrders'] = $data['pendingOrders']->map(function ($item, $key) {
			if (!empty($item['user']['photo']) && !Str::contains($item['user']['photo'], 'http')) {
				$item['user']['photo'] = url('/images/' . $item['user']['photo']);
			}
			$item['count'] = $item['orderDetailes']->count();
			unset($item['temp_o']);
			unset($item['address']);
			unset($item['promo_code']);
			unset($item['order_detailes']);
			unset($item['orderDetailes']);

			return $item;
		});
		// return response()->json($data['pendingOrders']);

		// dd($data['pendingOrders']);die;
		// // dd($data['topUsers']);die;

		return response()->json($data)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
			'X-Total-Count'                 => HomeData::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);

		$homeData = HomeData::all();

		if (is_null($homeData)) {
			return $this->sendError('HomeData not found.');
		}

		return response()->json($homeData)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
			'X-Total-Count'                 => HomeData::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);

	}

	/**
	 *
	 * te the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, HomeData $homeData) {
		$input = $request->all();
		$input = $input['values'];

		HomeData::where('name', 'about')->update(['value' => $input['about']]);

		HomeData::where('name', 'about_ar')->update(['value' => $input['about_ar']]);
		HomeData::where('name', 'contacts')->update(['value' => $input['contacts']]);
		HomeData::where('name', 'contacts_ar')->update(['value' => $input['contacts_ar']]);
		HomeData::where('name', 'facebook')->update(['value' => $input['facebook']]);
		HomeData::where('name', 'instagram')->update(['value' => $input['instagram']]);
		HomeData::where('name', 'snapchat')->update(['value' => $input['snapchat']]);
		HomeData::where('name', 'snapchat')->update(['value' => $input['snapchat']]);
		HomeData::where('name', 'discount')->update(['value' => $input['discount']]);
		HomeData::where('name', 'closed')->update(['value' => $input['closed']]);
		HomeData::where('name', 'closedMessage')->update(['value' => $input['closedMessage']]);
		HomeData::where('name', 'closedMessageOutTime')->update(['value' => $input['closedMessageOutTime']]);
		HomeData::where('name', 'open_from')->update(['value' => \Carbon\Carbon::parse($input['open_from'])->setTimezone('EET')->toDateTimeString()]);
		HomeData::where('name', 'open_to')->update(['value' => \Carbon\Carbon::parse($input['open_to'])->setTimezone('EET')->toDateTimeString()]);
		HomeData::where('name', 'valid_until')->update(['value' => \Carbon\Carbon::parse($input['valid_until'])->setTimezone('EET')->toDateString()]);
		HomeData::where('name', 'serve_until')->update(['value' => $input['serve_until']]);

		$homeData = HomeData::all();
		return response()->json($homeData)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
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
	public function destroy(HomeData $homeData) {
		$homeData->delete();

		return response()->json($homeData)->withHeaders([
			'Content-Range'                 => 'homeDatas 0-1/1',
			'X-Total-Count'                 => HomeData::count(),
			// 'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
}