<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProductsRate
 *
 * @property int $id
 * @property int $product_id
 * @property int $user_id
 * @property int $rate
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Product $product
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsRate whereUserId($value)
 * @mixin \Eloquent
 */
class ProductsRate extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function product() {
		return $this->hasOne(Product::class);
	}

	public function user() {
		return $this->hasOne(User::class);
	}

}
