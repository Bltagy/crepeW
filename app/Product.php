<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Product
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_ar
 * @property int|null $product_category_id
 * @property string|null $description
 * @property string|null $description_ar
 * @property int|null $pos_id
 * @property string|null $photo
 * @property string|null $photo_ar
 * @property string|null $mobile_photo
 * @property string|null $mobile_photo_ar
 * @property int|null $in_stock
 * @property int|null $featured
 * @property int $rate
 * @property float|null $small_size
 * @property float|null $medium_size
 * @property float|null $large_size
 * @property int $sort
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductsAdditionsRelations[] $addionCats
 * @property-read int|null $addion_cats_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductsAddition[] $addions
 * @property-read int|null $addions_count
 * @property-read \App\ProductCategory $category
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereDescriptionAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereInStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereLargeSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereMediumSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereMobilePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereMobilePhotoAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product wherePhotoAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product wherePosId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereProductCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereSmallSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function category() {
		return $this->belongsTo(ProductCategory::class);
	}

	public function addions() {
		return $this->belongsToMany(ProductsAddition::class, 'products_additions_relations', 'product_id', 'products_addition_id' );
	}

	public function addionCats() {
		return $this->hasMany(ProductsAdditionsRelations::class);
	}

}
