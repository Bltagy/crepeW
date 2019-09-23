<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Gallery
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_ar
 * @property string|null $photo
 * @property string|null $photo_ar
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery wherePhotoAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Gallery whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Gallery extends Model
{
	protected $table = 'gallery';

    protected $hidden = array('created_at', 'updated_at');

}
