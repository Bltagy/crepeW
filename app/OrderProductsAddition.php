<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\OrderProductsAddition
 *
 * @property int $id
 * @property int|null $order_detail_id
 * @property int $order_id
 * @property int $product_id
 * @property int $product_addition_id
 * @property int $quantity
 * @property float|null $price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Order $order
 * @property-read \App\OrderDetail $orderDetailes
 * @property-read \App\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereOrderDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereProductAdditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderProductsAddition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderProductsAddition extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function order() {
		return $this->belongsTo(Order::class);
	}

	public function orderDetailes() {
		return $this->belongsTo(OrderDetail::class);
	}

	public function product() {
		return $this->belongsTo(Product::class);
	}

}
