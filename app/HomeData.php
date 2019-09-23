<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\HomeData
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeData whereValue($value)
 * @mixin \Eloquent
 */
class HomeData extends Model
{
    protected $fillable = ['name','value' ];
    protected $hidden = array('created_at', 'updated_at');

}
