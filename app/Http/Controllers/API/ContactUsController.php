<?php

namespace App\Http\Controllers\API;

use App\ContactUsEntry;
use App\Http\Controllers\API\BaseController as BaseController;
use App\User;
use Illuminate\Http\Request;
use Validator;

class ContactUsController extends BaseController {


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
		$type        = ($input['type']) ?? false;
		$id_like      = ($input['id_like']) ?? false;
		$end          = ($input['_end']) ?? 10;
		$product_id   = ($input['product_id']) ?? false;
		$product_idss = ($input['product_idss']) ?? false;
		$lang         = ($request->header('X-lang')) ?? 'en';
		$web          = ($request->header('client-type')) ?? false;
		$mobile       = ($request->header('x-Device')) ? false : true;
		$from_date  = ($input['from_date']) ?? false;
		$to_date    = ($input['to_date']) ?? false;

		if ($web) {
			$promoCode = ContactUsEntry::limit(($end - $skip))->skip($skip)->orderBy($sort, $order);
		} else {
			$promoCode = ContactUsEntry::orderBy($sort, $order);
		}

		$promoCode = $promoCode->orderBy($sort, $order);

		if ($q) {
			$promoCode = $promoCode->Where('name', 'like', '%' . $q . '%')
				->OrWhere('email', 'like', '%' . $q . '%')
				->OrWhere('type', 'like', '%' . $q . '%')
				->OrWhere('message', 'like', '%' . $q . '%')
				->OrWhere('id', $q);
		}
		if ( $type ){
			switch ($type) {
				case 'contact':
					$promoCode = $promoCode->where('type', 'like', '%contact%')
					->OrWhere('type', 'like', '%تواصل%');
					break;
				case 'suggest':
					$promoCode = $promoCode->where('type', 'like', '%suggest%')
					->OrWhere('type', 'like', '%قتراح%');
					break;
				case 'complain':
					$promoCode = $promoCode->where('type', 'omplain', '%suggest%')
					->OrWhere('type', 'like', '%شكو%');
					break;
			}
		}
		if ($from_date) {
			$from   = \Carbon\Carbon::parse($from_date)->setTimezone('EET')->format('Y-m-d');
			$to     = ($to_date) ? \Carbon\Carbon::parse($to_date)->setTimezone('EET')->format('Y-m-d') : \Carbon\Carbon::now();
			$promoCode = $promoCode->whereBetween('created_at', [$from, $to]);
		}
		
		$promoCode = $promoCode->get();

		$promoCode = $promoCode->map(function ($item, $key) {
			if (!empty($item['photo'])) {
				$item['photo'] = url('/images/' . $item['photo']);
			} 
			return $item;

		});
		// $promoCode = $promoCode->map(function ($item, $key) {
		// 	$date = new \Carbon\Carbon;
		// 	$item['expired'] = ($date > $item['expire_at']) ? 1:0;
		// 	$item['applied'] = $item->taken->count();
		// 		return $item;
			
		// });



		return response()->json($promoCode)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ContactUsEntry::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);

	}


	/**
	 * @OA\Post(
	 *   path="/SendContactUs",
	 *   tags={"Contact Us"},
	 *   summary="Send the contact us form",
	 *   description="",
	 *   operationId="contactUs",
	 *   @OA\Parameter(
	 *     name="id",
	 *     description="User Id in case logged in",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="name",
	 *     description="full name",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="email",
	 *     description="User email",
	 *     in="query",
	 *     required=false,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="photo",
	 *     description="attached photo base64",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="type",
	 *     description="The type of message ( suggestion, complain or comment)",
	 *     in="query",
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="message",
	 *     description="The form message",
	 *     in="query",
	 *     required=true,
	 *     @OA\Schema(
	 *         type="string"
	 *     )
	 *   ),
	 *   @OA\Response(response="200",
	 *     description="The User object with token to use it on SMS verification message",
	 *   ),
	 *   @OA\Response(response="422",description="Validation Error"),
	 *   security={{
	 *     "petstore_auth": {"write:pets", "read:pets"}
	 *   }}
	 * )
	 */

	public function send(Request $request) {
		$input = $request->all();

		$validator = Validator::make($input, [
			'type' => 'required',
		]);

		if ($validator->fails()) {
			return $this->sendError('Validation Error.', $validator->errors());
		}

		if (!empty($input['id'])) {
			$user = User::find($input['id']);
			if (!$user) {
				return $this->sendError('User Not found.');
			}
			$input['name'] = $user->name;
			$input['email'] = $user->email;
		}



		$contats          = new ContactUsEntry;
		$contats->name    = $input['name'];
		$contats->email   = $input['email'];

		
		$image = $request->get('photo');
		if (explode(':', $image)[0] == 'data') {
			$name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];

			\Image::make($image)->save(public_path('images/') . $name);
			$contats->photo = $name;

		}

		$contats->type    = $input['type'];
		$contats->message = $input['message'];
		$contats->save();
		return response()->json($contats)->withHeaders([
			'Content-Range'                 => 'homeSliders 0-1/1',
			'X-Total-Count'                 => ContactUsEntry::count(),
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	public function update(Request $request, $id) {
		$input     = $request->all();
		
		$ContactUsEntry = ContactUsEntry::find($id);
		$ContactUsEntry->resolved = 1;
		$ContactUsEntry->save();

		return response()->json($ContactUsEntry)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
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
	public function destroy($id) {
		$product = ContactUsEntry::find($id);
		$product->delete();

		return response()->json($product)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ContactUsEntry::count(),
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
	public function upsdate(Request $request, $id) {
		$product = ContactUsEntry::find($id);
		$product->resolved = 1;
		$product->save();

		return response()->json($product)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ContactUsEntry::count(),
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
	public function show(Request $request, $id) {
		$product = ContactUsEntry::find($id);
		if (!empty($product->photo)) {
				$product->photo = url('/images/' . $product->photo);
			} 
		return response()->json($product)->withHeaders([
			'Content-Range'                 => 'products 0-1/1',
			'X-Total-Count'                 => ContactUsEntry::count(),
			//'Cache-Control'                 => 'max-age=604800, public',
			'Access-Control-Expose-Headers' => 'X-Total-Count',
		]);
	}

	

}