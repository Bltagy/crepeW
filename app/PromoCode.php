<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PromoCode
 *
 * @property int $id
 * @property string|null $promo_code
 * @property string|null $discount_type
 * @property int|null $amount
 * @property int|null $used_once
 * @property string|null $expire_at
 * @property int|null $expired
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\ProductCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UserPromoCode[] $taken
 * @property-read int|null $taken_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode wherePromoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PromoCode whereUsedOnce($value)
 * @mixin \Eloquent
 */
class PromoCode extends Model {
	protected $hidden = array('updated_at');

	public function category() {
		return $this->belongsTo(ProductCategory::class);
	}

	public function taken() {
		return $this->hasMany(UserPromoCode::class);
	}

}
