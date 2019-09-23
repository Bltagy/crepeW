<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\LangData;
use App\Product;
use App\PromoCode;
use Illuminate\Http\Request;
use App\Http\Traits\SMS;

class PromoCodeController extends BaseController {

	use LangData,SMS;
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$input = $request->all();

		$skip         = ($input['_start']) ?? 0;
		$sort         = ($input['_sort']) ?? 'id';
		$order        = ($input['_order']) ?? 'ASC';
		$q            = ($input['q']) ?? false;
		$price        = ($input['price']) ?? false;
		$id_like      = ($input['id_like']) ?? false;
		$end          = ($input['_end']) ?? 10;
		$product_id   = ($input['product_id']) ?? false;
		$product_idss = ($input['product_idss']) ?? false;
		$lang         = ($request->header('X-lang')) ?? 'en';
		$web          = ($request->header('client-type')) ?? false;
		$mobile       = ($request->header('x-Device')) ? false : true;

		if ($web) {
			$promoCode = PromoCode::limit(($end - $skip))->skip($skip)->orderBy($sort, $order);
		} else {
			$promoCode = PromoCode::orderBy($sort, $order);
		}

		$promoCode = $promoCode->orderBy($sort, $order);

		if ($q) {
			$promoCode = $promoCode->Where('name', 'like', '%' . $q . '%')
				->OrWhere('name_ar', 'like', '%' . $q . '%')
				->OrWhere('description', 'like', '%' . $q . '%')
				->OrWhere('description_ar', 'like', '%' . $q . '%')
				->OrWhere('id', $q);
		}
		$promoCode = $promoCode->get();
		$promoCode = $promoCode->map(function ($item, $key) {

			$date = new \Carbon\Carbon;
			$item['expired'] = ($date > $item['expire_at']) ? 1:0;
			$item['applied'] = $item->taken->count();
				return $item;
			
		});



		return response()->json($promoCode)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => PromoCode::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$input = $request->all();

		$promo = new PromoCode;

		if (isset($input['discount_type'])) {
			$promo->discount_type = $input['discount_type'];
		}
		if (isset($input['amount'])) {
			$promo->amount = $input['amount'];
		}
		if (isset($input['used_once'])) {
			$promo->used_once = $input['used_once'];
		}
		if (isset($input['expire_at'])) {
			$promo->expire_at = \Carbon\Carbon::parse($input['expire_at'])->setTimezone('EET')->format('Y-m-d');
		}

		$promo_code        = $this->generatePromo();
		$promo->promo_code = $promo_code;
		$promo->save();
		$massege = "A promo code ($promo->promo_code) has been created with discount($promo->amount) and expired at ($promo->expire_at)";
		$this->sendSMS('01000003383', $massege);
		return response()->json($promo)->withHeaders([
			'Content-Range'                 => 'PromoCode 0-1/1',
			'X-Total-Count'                 => PromoCode::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}
	/**
	 * @OA\Get(
	 *   path="/verifyPromoCode/{promo_code}",
	 *   tags={"Orders"},
	 *   summary="Check the promo code status",
	 *   operationId="verifyPromoCode",
	 *    @OA\Response(response="200",
	 *     description="",
	 *   ),
	 *    @OA\Response(response="422",
	 *     description="Promo code not found.",
	 *   )
	 * )
	 */
	public function verifyPromoCode(Request $request, $promo_code) {
		$lang  = ($request->header('X-lang')) ?? 'en';
		$promo = PromoCode::wherePromoCode($promo_code)->first();

		if (empty($promo->id)) {
			return $this->sendError(\Lang::get('order.promocode_not_found', [], $lang));
		}

		if ($promo->expired) {
			return $this->sendError(\Lang::get('order.promocode_expired', [], $lang));
		}

		$date = new \Carbon\Carbon;
		if ($date > $promo->expire_at) {
			return $this->sendError(\Lang::get('order.promocode_expired', [], $lang));
		}

		if ($promo->used_once == 1 && $promo->taken->count()) {
			return $this->sendError(\Lang::get('order.promocode_taken', [], $lang));
		}

		return response()->json($promo)->withHeaders([
			'Content-Range'                 => 'PromoCode 0-1/1',
			'X-Total-Count'                 => PromoCode::count(),
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
		$area = PromoCode::find($id);
		$area->delete();

		return response()->json($area)->withHeaders([
			'Content-Range'                 => 'area 0-1/1',
			'X-Total-Count'                 => PromoCode::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

}
