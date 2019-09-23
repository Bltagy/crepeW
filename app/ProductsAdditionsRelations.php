<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProductsAdditionsRelations
 *
 * @property int $id
 * @property int $products_addition_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Product $product
 * @property-read \App\ProductsAddition $productsAddition
 * @property-read \App\ProductsAdditionCategory $productsAdditionCats
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations whereProductsAdditionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionsRelations whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductsAdditionsRelations extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function product() {
		return $this->belongsTo(Product::class);
	}


	public function productsAddition() {
		return $this->belongsTo(ProductsAddition::class);
	}

	public function productsAdditionCats() {
		return $this->belongsTo(ProductsAdditionCategory::class, 'products_addition_id');
	}

}
