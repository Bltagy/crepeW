<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProductsAddition
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $name_ar
 * @property string|null $description_ar
 * @property float|null $price
 * @property string|null $photo
 * @property int $pos_id
 * @property int|null $addition_category_id
 * @property int $parent_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\ProductsAdditionCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductsAdditionsRelations[] $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereAdditionCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition wherePosId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAddition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductsAddition extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function products() {
		return $this->hasMany(ProductsAdditionsRelations::class);
	}

	public function category() {
		return $this->belongsTo(ProductsAdditionCategory::class, 'addition_category_id');
	}
}
