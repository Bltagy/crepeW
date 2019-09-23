<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProductsAdditionCategory
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_ar
 * @property string|null $photo
 * @property int|null $pos_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductsAddition[] $additions
 * @property-read int|null $additions_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory wherePosId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProductsAdditionCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductsAdditionCategory extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function additions() {
		return $this->hasMany(ProductsAddition::class, 'addition_category_id');
	}
}
