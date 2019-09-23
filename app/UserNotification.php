<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserNotification
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $title
 * @property string|null $body
 * @property string|null $photo
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserNotification whereUserId($value)
 * @mixin \Eloquent
 */
class UserNotification extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function user() {
		return $this->belongsTo(User::class);
	}

}
