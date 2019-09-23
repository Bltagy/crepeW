<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\HomeSlider
 *
 * @property int $id
 * @property string $title
 * @property string|null $title_ar
 * @property string|null $photo_ar
 * @property string|null $photo
 * @property int $for_mobile
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider whereForMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider wherePhotoAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider whereTitleAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\HomeSlider whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HomeSlider extends Model
{
    protected $fillable = ['title','sub_title','photo' ];
}
