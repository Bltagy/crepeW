<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProductCategory
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_ar
 * @property string|null $photo
 * @property string|null $photo_ar
 * @property int|null $pos_id
 * @property int $hide
 * @property int $sort
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Product[] $product
 * @property-read int|null $product_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereHide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory wherePhotoAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory wherePosId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductCategory extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function product() {
		return $this->hasMany(Product::class)->orderBy('sort');
	}

}
