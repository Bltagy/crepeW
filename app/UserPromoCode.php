<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserPromoCode
 *
 * @property int $id
 * @property int $promo_code_id
 * @property int $user_id
 * @property int $order_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Order $order
 * @property-read \App\PromoCode $promoCode
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode wherePromoCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserPromoCode whereUserId($value)
 * @mixin \Eloquent
 */
class UserPromoCode extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function promoCode() {
		return $this->belongsTo(PromoCode::class);
	}

	public function order() {
		return $this->belongsTo(Order::class);
	}

}
