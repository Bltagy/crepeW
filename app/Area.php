<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Area
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $name_ar
 * @property int|null $fees
 * @property string|null $lat_long
 * @property string|null $serve_until
 * @property int $pos_id
 * @property string|null $destination_branch
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereDestinationBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereLatLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereNameAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area wherePosId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereServeUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Area whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Area extends Model {
	protected $hidden = array('created_at', 'updated_at', 'lat_long');


}
