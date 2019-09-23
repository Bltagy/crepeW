<?php

namespace App\Http\Controllers\API;

use App\Area;
use App\HomeData;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Traits\SMS;
use App\Order;
use App\OrderDetail;
use App\OrderProductsAddition;
use App\Product;
use App\ProductsAddition;
use App\PromoCode;
use App\ServiceRate;
use App\User;
use App\UserPromoCode;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lang;
use stdClass;
use Validator;

class OrderController extends BaseController
{

    use SMS;

    public static function checkOrderStatus()
    {
        $db2ID = DB::connection('mysql2')->table('Orders')
            ->where('OrderStatus', '!=', 'Initiated')
            ->where('OrderStatus', '!=', 'Deleted')
            ->get();

        foreach ($db2ID as $key => $value) {
            $order = Order::find($value->OrderID);
            if ($order) {
                if ($order->order_status != $value->OrderStatus && in_array($value->OrderStatus, ['Assign', 'Completed', 'Canceled'])) {
                    $order->order_status = $value->OrderStatus;
                    $order->save();
                    $statusLang = [
                        "Initiated" => "",
                        "Assign" => "قيد التنفيذ",
                        "Completed" => "اكتمل",
                    ];
                    $type = "pending";
                    if ($value->OrderStatus == 'Assign') {
                        $type = "pending";
                        $notification = ['user_id' => $order->user_id, 'title' => 'تم تغيير حالة طلبك', 'body' => 'تم استلام الطلب وجارى التحضير'];
                    }
                    if ($value->OrderStatus == 'Completed') {
                        $type = "completed";
                        $notification = ['user_id' => $order->user_id, 'title' => 'تم تغيير حالة طلبك', 'body' => 'تم خروج الطلب من المطعم مع مندوب التوصيل'];
                    }

                    if ($value->OrderStatus == 'Canceled') {
                        $type = "canceled";
                        $notification = ['user_id' => $order->user_id, 'title' => 'تم الغاء طلبكم', 'body' => 'تم حذف الاوردر من قبل الاداره للاستفسار اتصل ٠١٠٠٦٠٨٨٨٦٠'];
                    }
                    NotificationController::Send($notification['title'], $notification['body'], $notification['user_id'], false, $type);

                }
            }

        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Responsecd ../
     *
     */
    public function index(Request $request)
    {
        $input = $request->all();

        $skip = ($input['_start']) ?? 0;
        $sort = ($input['_sort']) ?? 'id';
        $order = ($input['_order']) ?? 'ASC';
        $ordered_on = ($input['ordered_on']) ?? false;
        $from_date = ($input['from_date']) ?? false;
        $to_date = ($input['to_date']) ?? false;
        $user = ($input['user_id']) ?? false;
        $end = ($input['_end']) ?? 10;
        $q = ($input['q']) ?? false;
        if ($sort == 'serviceRate') {
            $sort = 'id';
        }

        $orders = Order::skip($skip)->take(10)->orderBy($sort, $order);

        if ($request->header('Client-Type') == 'web') {
            $orders = Order::orderBy($sort, $order);

        } else {
            $orders = Order::orderBy($sort, $order);
        }

        if ($q) {
            $orders = $orders->OrWhere('id', 'like', '%' . $q . '%')
                ->OrWhere('address', 'like', '%' . $q . '%')
                ->OrWhere('notes', 'like', '%' . $q . '%')
                ->OrWhere('order_status', 'like', '%' . $q . '%');
        }

        if ($user) {
            $orders = $orders->OrWhere('user_id', 'like', '%' . $user . '%');
        }
        if ($ordered_on) {
            $ordered_on = Carbon::parse($ordered_on)->setTimezone('EET')->format('Y-m-d');
            $orders = $orders->Where('created_at', 'like', $ordered_on . '%');
        }
        if ($from_date) {
            $from = Carbon::parse($from_date)->setTimezone('EET')->format('Y-m-d');
            $to = ($to_date) ? Carbon::parse($to_date)->setTimezone('EET')->format('Y-m-d') : Carbon::now();
            $orders = $orders->whereBetween('created_at', [$from, $to]);
        }
        if ($user) {
            $orders = $orders->Where('user_id', 'like', '%' . $user . '%');
        }

        $count = $orders->count();
        $orders = $orders->limit(($end - $skip))->skip($skip);
        $orders = $orders->get();
        $orders = $orders->map(function ($item, $key) {

            $item['grand_totlal'] = ($item['total_after_discount'] < 0) ? $item['fees'] : ceil($item['total_after_discount'] + $item['fees']);
            $rate = ServiceRate::where('order_id', $item['id'])->pluck('rate', 'service_name')->toArray();
            $rat[0] = [
                'waffle' => ($rate) ? $rate['waffle'] : "not yet",
                'crepe' => ($rate) ? $rate['crepe'] : "not yet",
                'delivery' => ($rate) ? $rate['delivery'] : "not yet",
            ];
            $item['serviceRate'] = $rat;
            return $item;
        });
        return response()->json($orders)->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => $count,
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    /**
     * @OA\Post(
     *   path="/submitOrder",
     *   tags={"Orders"},
     *   summary="Submit An order",
     *     description="the object that have the rate it should look like

    {'user_id':'276','area_id':'2','fees':'10','discount_type':'(global, promoCode, both or none )','promo_code':'ds2ss','discount_amount':'55','total_price':'520','total_after_discount':'420','notes':'notes','products':[{'id':'2','quantity':'2','price':'220','notes':'notes','additions':[{'id':'2','price':'22'}]},{'id':'4','quantity':'1','price':'44','notes':'notes','additions':[{'id':'10','price':'12'},{'id':'11','price':'12'}]}],'address':'home_number    zone_name    zone_id    floor_number    apartment_number    notes'}",
     *   operationId="submitOrder",
     *   @OA\Parameter(
     *     name="user_id",
     *     description="User ID ( already retrieved )",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *   ),@OA\Parameter(
     *     name="products",
     *     description="the products object includes quantities and total prices",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),@OA\Parameter(
     *     name="address",
     *     description="should be one of ( address, home_address or office_address) ",
     *     in="query",
     *     required=false,
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *    @OA\Response(response="200",
     *     description="",
     *   ),
     *   @OA\Response(response="422",description="Validation Error"),
     *   security={{
     *     "petstore_auth": {"write:pets", "read:pets"}
     *   }}
     * )
     */

    public function submitOrder(Request $request)
    {

        $input = $request->all();
        $adress = $input['address'];
        $adress = str_replace('   ', '|||', $adress);
        $area_id = explode('|||', $adress);
        $area = Area::find($area_id[0]);
        $lang = ($request->header('X-lang')) ?? 'en';
        $device = ($input['X-Device']) ?? false;

        if (!$area->is_24 && $this->isAreaServed($area->serve_until)) {
            return $this->sendError(HomeData::where('name', 'serve_until')->first()->value);
        }

        if ($closedMessage = $this->isWorkingHorus()) {
            return $this->sendError($closedMessage);
        }
        if (!empty($input['promo_code'])) {
            $promo = PromoCode::where('promo_code', $input['promo_code'])->first();

            if (empty($promo->id)) {
                return $this->sendError('Promo code not found.');
            }
            $date = new Carbon;
            if ($date > $promo->expire_at) {
                return $this->sendError('Promo code expired.');
            }

            if ($promo->used_once == 1 && $promo->taken->count()) {
                return $this->sendError('The promo code is already taken!');
            }

        }

        $latestOrder = Order::where('user_id', $input['user_id'])->orderBy('created_at', 'DESC')->first();
        if ($latestOrder) {

            $latestOrderDate = Carbon::parse($latestOrder->created_at)->addMinutes(5);
            $date = new Carbon;
            if ($date < $latestOrderDate) {
                return $this->sendError('عفوا لا يمكن طلب اوردر جديد الا بعد مرور 30 دقيقة من اخر اوردر.');
            }
        }

        $order = new Order;
        $order->user_id = $input['user_id'];
        $order->total_price = $input['total_price'];
        $order->address = $input['address'];
        $order->notes = $input['notes'];
        $order->temp_o = serialize($input);
        $order->order_status = 'Initiated';
        $order->fees = $input['fees'];
        $order->promo_code = (!empty($input['promo_code'])) ? $input['promo_code'] : null;
        $order->general_discount = HomeData::where('name', 'discount')->first()->value;
        $order->total_after_discount = ceil($input['total_after_discount']);
        $order->save();
        // $order->created_at = \Carbon\Carbon::parse($order->created_at)->addHours(2)->getTimestamp();
        // $order->save();

        // asd
        if ($order->id && !empty($input['promo_code'])) {

            $storePromo = new UserPromoCode;
            $storePromo->order_id = $order->id;
            $storePromo->user_id = $input['user_id'];
            $storePromo->promo_code_id = $promo->id;
            $storePromo->save();
            $user = User::find($input['user_id']);
            $massege = "The promo code ($promo->promo_code) has been applied for user '$user->name(ID $user->id)' with discount($promo->amount)";
            $this->sendSMS('01000003383', $massege);
            // return response()->json('no	');
        }
        // return response()->json('OK');

        foreach ($input['products'] as $key => $product) {

            $orderDetailes = new OrderDetail;
            $orderDetailes->order_id = $order->id;

            $orderDetailes->product_id = $product['id'];

            $orderDetailes->quantity = $product['quantity'];

            if (!empty($product['size'])) {
                $orderDetailes->size = $product['size'];
            }

            if (!empty($product['size_price'])) {
                $orderDetailes->size_price = $product['size_price'];
            }

            if (!empty($product['notes'])) {
                $orderDetailes->notes = $product['notes'];
            }

            $orderDetailes->save();
            if (!empty($product['additions'])) {
                foreach ($product['additions'] as $key => $p_addtion) {
                    $additions = new OrderProductsAddition;
                    $additions->order_detail_id = $orderDetailes->id;
                    $additions->order_id = $order->id;
                    $additions->product_id = $product['id'];
                    $additions->product_addition_id = $p_addtion['id'];
                    $additions->price = $p_addtion['price'];
                    $additions->save();
                }
            }

        }
        $uids = HomeData::where('name', 'skip_ids')->first()->value;
        if ($uids) {
            $uids = explode(',', $uids);
            if (in_array($input['user_id'], $uids)) {
                return $this->sendResponse($order, 'Custom success');
            }
        }

        $notification = ['user_id' => $order->user_id, 'title' => 'Your order has been send', 'body' => 'Your order in progress and will be delivers within 30 minutes, thank you for using our service'];
        $posProducts = [];
        foreach ($input['products'] as $key => $product) {
            $productOb = Product::find($product['id']);
            $productAdds = [];
            foreach ($product['additions'] as $add) {
                $addition = ProductsAddition::find($add['id']);
                $productAdds[] = [
                    'ExtraID' => (int)$addition->pos_id,
                    'price' => (float)$add['price'],
                    'quantity' => $product['quantity'],
                ];
            }
            $posProducts[] = [
                'ProductID' => (int)$productOb->pos_id,
                'product_name' => $productOb->name_ar,
                'quantity' => (int)$product['quantity'],
                'comment' => $product['notes'],
                'price' => (float)$productOb->medium_size,
                'order_products_addition' => $productAdds,
            ];
        }

        $orderToPos = [];
        $address = explode('   ', $input['address']);
        $area = Area::find($input['area_id']);
        if ($input['discount_type'] == 'global') {
            $discount_type = 'percentage';
        } elseif ($input['discount_type'] == 'promoCode') {
            $discount_type = 'fixed';
        } elseif ($input['discount_type'] == 'both') {
            $discount_type = 'percentage_and_fixed';
        } else {
            $discount_type = 'none';
        }

        $orderToPos['order_id'] = $order->id;
        $user = User::find($input['user_id']);
        $orderToPos['user_name'] = $user->name;
        $orderToPos['title'] = ($user->gender == 'female') ? 2 : 1;
        $orderToPos['user_mobile'] = $user->mobile_number;
        $orderToPos['address'] = [
            'home_number' => $this->convert($address[2]),
            'zone_name' => $area->name_ar,
            'zone_id' => $this->convert($area->pos_id),
            'floor_number' => $this->convert($address[3]),
            'apartment_number' => 0,
            'full_address_details' => $address[5],
        ];
        $sum = (int)$input['total_price'] - (int)$input['discount_amount'];
        $discount_amount = ($sum < 0) ? $input['total_price'] : $input['discount_amount'];

        $orderToPos['fees'] = (int)$input['fees'];
        $orderToPos['discount_type'] = $discount_type;
        $orderToPos['discount_amount'] = $discount_amount;
        $orderToPos['total_price_before_discount'] = $input['total_price'];
        $orderToPos['total_price_after_discount'] = ($sum < 0) ? 0 : $input['total_after_discount'];
        // $orderToPos['order_status']                = 'Initiated';
        $orderToPos['created_at'] = $order->created_at;
        $orderToPos['notes'] = $order->notes;
        $orderToPos['order_products'] = $posProducts;
        $toPosOrder = [];
        $toPosOrder = $orderToPos;
        $global = HomeData::where('name', 'discount')->first()->value;
        $promo = (!empty($input['promo_code'])) ? PromoCode::where('promo_code', $input['promo_code'])->first()->amount : 0;
        $db2ID = DB::connection('mysql2')->table('Orders')->insertGetId([
            'OrderID' => $toPosOrder['order_id'],
            'CustomerName' => $toPosOrder['user_name'],
            'TitleName' => $toPosOrder['title'],
            'Phone1' => $toPosOrder['user_mobile'],
            'HomeNum' => $toPosOrder['address']['home_number'],
            'FloorNum' => $toPosOrder['address']['floor_number'],
            'ApartmentNum' => $toPosOrder['address']['apartment_number'],
            'AddressNotice' => $toPosOrder['address']['full_address_details'],
            'ZoneName' => $toPosOrder['address']['zone_name'],
            'Subtotal' => $toPosOrder['total_price_before_discount'],
            'StreetName' => $address[1],
            'DeliveryFees' => $toPosOrder['fees'],
            'OrderNotes' => $toPosOrder['notes'],
            'Discount' => $toPosOrder['discount_amount'],
            'DiscountPercentage' => ($global) ? $global : 0,
            'FixedDiscount' => ($promo) ? $promo : 0,
            'GrandTotal' => abs($toPosOrder['total_price_after_discount']) + $toPosOrder['fees'],
            'ItemCount' => count($toPosOrder['order_products']),
        ]);

        foreach ($toPosOrder['order_products'] as $key => $value) {
            $db2DetailsID = DB::connection('mysql2')->table('OrderDetails')->insertGetId([
                'OrderID' => $db2ID,
                'ItemID' => $value['ProductID'],
                'Quantity' => $value['quantity'],
                'ProductNotes' => $value['comment'],
                'Price' => $value['price'],
                'Total' => $value['price'] * $value['quantity'],
            ]);
            if (!empty($value['order_products_addition'])) {
                foreach ($value['order_products_addition'] as $value2) {
                    $db2AddiaonsID = DB::connection('mysql2')->table('OrderDetailsExtera')->insertGetId([
                        'OrderID' => $db2ID,
                        'OrderDetailsID' => $db2DetailsID,
                        'ItemID' => $value['ProductID'],
                        'ExtraID' => $value2['ExtraID'],
                        'Quantity' => $value2['quantity'],
                        'Price' => $value2['price'],
                        'Total' => $value2['price'] * $value2['quantity'],
                    ]);
                }
            }

        }

        $notification = ['user_id' => $order->user_id, 'title' => Lang::get('order.order_submitted', [], $lang), 'body' => Lang::get('order.order_submitted_body', [], $lang)];
        NotificationController::Send($notification['title'], $notification['body'], $notification['user_id'], false, 'pending');
        return $this->sendResponse($orderToPos, Lang::get('order.order_submitted', [], $lang));
    }

    public function isAreaServed($servedUntill)
    {
        // $date = new \Carbon\Carbon;
        // $casablanca = \Carbon\Carbon::createFromTimeString($servedUntill);
        // $isAreaServed  = $date->lt($casablanca);

        $now = new Carbon;

        $fromD = HomeData::where('name', 'open_from')->first()->value;
        // $toD   = HomeData::where('name', 'open_to')->first()->value;
        $toD = $servedUntill;

        $fromComp = Carbon::parse($fromD)->setTimezone('EET')->format('H');
        $toComp = Carbon::parse($toD)->setTimezone('EET')->format('H');

        $fromPars = Carbon::parse($fromD);

        $daysDiff = $fromPars->diffInDays($now);
        $difHours = (($toComp - $fromComp) < 0) ? 24 + ($toComp - $fromComp) : $toComp - $fromComp;

        $fromMin = Carbon::parse($fromD)->setTimezone('EET')->format('i');
        $toMin = Carbon::parse($toD)->setTimezone('EET')->format('i');
        $mDiff = $toMin - $fromMin;

        $fromNow = Carbon::parse($fromD)->addDays($daysDiff);
        if ($mDiff < 0) {
            $toNow = Carbon::parse($fromD)->addDays($daysDiff)->addHours($difHours)->subMinutes(abs($mDiff));
        } else {
            $toNow = Carbon::parse($fromD)->addDays($daysDiff)->addHours($difHours)->addMinutes(abs($mDiff));
        }
        if (($fromNow <= $now) && ($now <= $toNow)) {
            return false;
        } else {
            return true;
        }
    }

    public function isWorkingHorus()
    {

        if (HomeData::where('name', 'closed')->first()->value == 1) {
            return HomeData::where('name', 'closedMessage')->first()->value;
        }
        $now = new Carbon;
        $fromD = HomeData::where('name', 'open_from')->first()->value;
        $toD = HomeData::where('name', 'open_to')->first()->value;

        $fromComp = Carbon::parse($fromD)->setTimezone('EET')->format('H');
        $toComp = Carbon::parse($toD)->setTimezone('EET')->format('H');

        $fromPars = Carbon::parse($fromD);

        $daysDiff = $fromPars->diffInDays($now);
        $difHours = (($toComp - $fromComp) < 0) ? 24 + ($toComp - $fromComp) : $toComp - $fromComp;

        $fromMin = Carbon::parse($fromD)->setTimezone('EET')->format('i');
        $toMin = Carbon::parse($toD)->setTimezone('EET')->format('i');
        $mDiff = $toMin - $fromMin;

        $fromNow = Carbon::parse($fromD)->addDays($daysDiff);
        if ($mDiff < 0) {
            $toNow = Carbon::parse($fromD)->addDays($daysDiff)->addHours($difHours)->subMinutes(abs($mDiff));
        } else {
            $toNow = Carbon::parse($fromD)->addDays($daysDiff)->addHours($difHours)->addMinutes(abs($mDiff));
        }

        if (($fromNow <= $now) && ($now <= $toNow)) {
            return false;
        } else {
            return HomeData::where('name', 'closedMessageOutTime')->first()->value;
        }
    }

    public function convert($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    public function show(Request $request, $id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Product not found.');
        }

        $order = Order::where('id', $id)->with('orderDetailes.OrderProductsAddition')->get()->toArray();
        $address = explode('   ', $order[0]['address']);
        $area = Area::find($address[0])->name;
        $order[0]['address'] = str_replace($address[0], $area . "   ", $order[0]['address']);
        foreach ($order as &$value) {
            foreach ($value['order_detailes'] as &$value2) {
                $product = Product::find($value2['product_id']);
                $value2['product_name'] = $product->name;
                $value2['price'] = $product->medium_size;
                foreach ($value2['order_products_addition'] as &$value3) {
                    $value3['product_addition_name'] = ProductsAddition::find($value3['product_addition_id'])->name;
                }
            }
        }

        $order[0]['grand_totlal'] = $order[0]['total_after_discount'] + $order[0]['fees'];
        // $order = $this->array_to_object($order[0]);
        return response()->json($order[0])->withHeaders([
            'Content-Range' => 'products 0-1/1',
            'X-Total-Count' => Order::count(),
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);

    }

    /**
     * @OA\Post(
     *   path="/serviceRate",
     *   tags={"Orders"},
     *   summary="Submit a rate for a service",
     *   description="Authentication required on header using token",
     *   operationId="serviceRate",
     *   @OA\Parameter(
     *     name="user_id",
     *     description="User ID ( already retrieved )",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *   ),@OA\Parameter(
     *     name="service_rate",
     *     description="the object that have the rate it should look like

    {'crepe':5,'waffle ':5,'delivery':5} ",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),@OA\Parameter(
     *     name="order_id",
     *     description="The Order id that you got from submitting order",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *   ),
     *    @OA\Response(response="200",
     *     description="",
     *   ),
     *   @OA\Response(response="422",description="Validation Error"),
     *   security={{
     *     "petstore_auth": {"write:pets", "read:pets"}
     *   }}
     * )
     */

    public function serviceRate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
            'order_id' => 'required',
            'service_rate' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($input['user_id']);

        if (!$user) {
            return $this->sendError('User doesn\'t exists.', '', 404);
        }
        if (!empty($input['service_rate'])) {
            foreach ($input['service_rate'] as $key => $rate) {
                $service_rate = New ServiceRate;
                $service_rate->service_name = $key;
                $service_rate->user_id = $input['user_id'];
                $service_rate->order_id = $input['order_id'];
                $service_rate->rate = $rate;
                $service_rate->save();
            }
        }

        return $this->sendResponse([], 'Rate submitted successfully.');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'cart' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order = new Order;
        // $order->temp_o = serialize($input);
        $order->save();
        return $this->sendResponse($order, 'Order submitted successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function history($id)
    {
        $order_history =
            ["orders" =>
                [
                    [
                        'order_id' => 8464,
                        'order_date' => "22 / 12 / 2018",
                        'order_status' => 'delivered',
                        'order_items' => [
                            ['name' => 'Product A',
                                'quantity' => 10,
                                'total_price' => 180,
                            ],
                            ['name' => 'Product A',
                                'quantity' => 10,
                                'total_price' => 180,
                            ],
                            ['name' => 'Product A',
                                'quantity' => 10,
                                'total_price' => 180,
                            ],
                        ],
                    ],
                    [
                        'order_id' => 8464,
                        'order_date' => "22 / 12 / 2018",
                        'order_status' => 'delivered',
                        'order_items' => [
                            ['name' => 'Product A',
                                'quantity' => 10,
                                'total_price' => 180,
                            ],
                            ['name' => 'Product A',
                                'quantity' => 10,
                                'total_price' => 180,
                            ],
                            ['name' => 'Product A',
                                'quantity' => 10,
                                'total_price' => 180,
                            ],
                        ],
                    ],

                ],

            ];
    }

    /**
     * @OA\Get(
     *   path="/orderHistory/{user_id}",
     *   tags={"Orders","User"},
     *   summary="list all orders for a user",
     *   operationId="orderHistory",
     *   @OA\Parameter(
     *     name="user_id",
     *     description="user id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *   ),
     *    @OA\Response(response="200",
     *     description="
    [{'id':1,'user_id':285,'total_price':'541','temp_o':null,'address':'home_address','notes':'Example of notes','fees':'5','order_status':'pending','delivery_date':null,'order_detailes':[{'id':1,'order_id':1,'product_id':2,'quantity':1,'price':null,'comment':null,'notes':null,'size':'Small','size_price':'1','order_products_addition':[{'id':1,'order_detail_id':1,'order_id':1,'product_id':2,'product_addition_id':1,'quantity':1,'price':'150.0','product_addition_name':'Cheese'}],'product_name':'Crepe Name'},{'id':2,'order_id':1,'product_id':4,'quantity':5,'price':null,'comment':null,'notes':null,'size':null,'size_price':null,'order_products_addition':[{'id':2,'order_detail_id':2,'order_id':1,'product_id':4,'product_addition_id':1,'quantity':1,'price':'150.0','product_addition_name':'Cheese'},{'id':3,'order_detail_id':2,'order_id':1,'product_id':4,'product_addition_id':2,'quantity':1,'price':'180.0','product_addition_name':'Sauce'}],'product_name':'Crepe Name'}]}]
     *     ",
     *   )
     * )
     */

    public function orderHistory(Request $request, $user_id)
    {
        $lang = ($request->header('X-lang')) ?? 'en';
        $user = User::find($user_id);
        if (!$user) {
            return $this->sendError('User doesn\'t exists.', '', 404);
        }

        $statusLang = [
            "pending" => "قيد الموافقة",
            "Initiated" => "قيد الموافقة",
            "Assign" => "قيد التنفيذ",
            "Completed" => "اكتمل",
            "Canceled" => "ملغي",
        ];
        if ($lang == 'en') {
            $statusLang = [
                "pending" => "pending",
                "Initiated" => "Initiated",
                "Assign" => "Assign",
                "Completed" => "Completed",
                "Canceled" => "Canceled",
            ];
        }
        // $order = Order::where('user_id', $user_id)->with('orderDetailes.OrderProductsAddition')->get();
        $current = Order::where('user_id', $user_id)->orderBy('created_at', 'DESC')->where('order_status', '!=', 'Completed')->with('orderDetailes.OrderProductsAddition')->get()->toArray();
        foreach ($current as &$value) {
            $value['order_status'] = $statusLang[$value['order_status']];
            $value['total_after_discount'] = $value['total_after_discount'] + $value['fees'];
            foreach ($value['order_detailes'] as &$value2) {
                $prod = Product::find($value2['product_id']);
                $value2['product_name'] = ($prod) ? $prod->name_ . $lang : "";
                foreach ($value2['order_products_addition'] as &$value3) {
                    $adds = ProductsAddition::find($value3['product_addition_id']);
                    $value3['product_addition_name'] = ($adds) ? $adds->name_ . $lang : "";
                }
            }
        }

        $history = Order::where('user_id', $user_id)->orderBy('created_at', 'DESC')->where('order_status', 'Completed')->with('orderDetailes.OrderProductsAddition')->get()->toArray();

        foreach ($history as &$value) {
            $value['order_status'] = $statusLang[$value['order_status']];
            $value['total_after_discount'] = $value['total_after_discount'] + $value['fees'];
            $value['created_at'] = $value['updated_at'];
            foreach ($value['order_detailes'] as &$value2) {
                $prod = Product::find($value2['product_id']);
                $value2['product_name'] = ($prod) ? $prod->name_ . $lang : "";
                foreach ($value2['order_products_addition'] as &$value3) {
                    $adds = ProductsAddition::find($value3['product_addition_id']);
                    $value3['product_addition_name'] = ($adds) ? $adds->name_ . $lang : "";
                }
            }
        }
        $result = ['current' => $current, 'history' => $history];

        return response()->json($result)->withHeaders([
            'Content-Range' => 'users 0-1/1',
            'X-Total-Count' => Order::count(),
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    public function array_to_object($array)
    {
        $obj = new stdClass;
        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{$k} = $this->array_to_object($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, Order $Order)
    {
        $input = $request->all();

        $order = Order::find($input['id']);

        if (is_null($order)) {
            return $this->sendError('Order not found.');
        }

        if (isset($input['order_status'])) {
            $order->order_status = $input['order_status'];
        }

        $order->save();

        return response()->json($order)->withHeaders([
            'Content-Range' => 'orders 0-1/1',
            'X-Total-Count' => 15,
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }
}
