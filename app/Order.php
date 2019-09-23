<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Order
 *
 * @property int $id
 * @property int|null $user_id
 * @property float|null $total_price
 * @property float|null $total_after_discount
 * @property string|null $promo_code
 * @property string|null $general_discount
 * @property string|null $temp_o
 * @property string|null $address
 * @property string|null $notes
 * @property float|null $fees
 * @property string|null $order_status
 * @property string|null $delivery_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrderDetail[] $orderDetailes
 * @property-read int|null $order_detailes_count
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereGeneralDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order wherePromoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTempO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTotalAfterDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereUserId($value)
 * @mixin \Eloquent
 */
class Order extends Model {
	// protected $hidden = array('updated_at');

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function orderDetailes() {
		return $this->hasMany(OrderDetail::class);
	}

}
