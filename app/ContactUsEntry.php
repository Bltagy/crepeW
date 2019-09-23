<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ContactUsEntry
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $type
 * @property string|null $message
 * @property string|null $photo
 * @property int $resolved
 * @property string|null $source
 * @property string|null $order_number
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContactUsEntry whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ContactUsEntry extends Model {
	protected $hidden = array('updated_at');

}
