<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\OrderDetail
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property float|null $price
 * @property string|null $comment
 * @property string|null $notes
 * @property string|null $size
 * @property float|null $size_price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrderProductsAddition[] $OrderProductsAddition
 * @property-read int|null $order_products_addition_count
 * @property-read \App\Order $order
 * @property-read \App\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereSizePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderDetail extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function order() {
		return $this->belongsTo(Order::class);
	}

	public function OrderProductsAddition() {
		return $this->hasMany(OrderProductsAddition::class);
	}

	public function product() {
		return $this->belongsTo(Product::class);
	}

}
