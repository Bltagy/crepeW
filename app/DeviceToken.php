<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\DeviceToken
 *
 * @property int $id
 * @property string|null $device_id
 * @property string|null $token
 * @property int|null $user_id
 * @property int|null $fcmData
 * @property int $ios
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereFcmData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereIos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceToken whereUserId($value)
 * @mixin \Eloquent
 */
class DeviceToken extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function user() {
		return $this->belongsTo(User::class);
	}

}
