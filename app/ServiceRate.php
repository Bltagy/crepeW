<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ServiceRate
 *
 * @property int $id
 * @property string|null $service_name
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int $rate
 * @property string|null $tmp
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Order $order
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereTmp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServiceRate whereUserId($value)
 * @mixin \Eloquent
 */
class ServiceRate extends Model {
	protected $hidden = array('created_at', 'updated_at');

	public function order() {
		return $this->hasOne(Order::class);
	}

	public function user() {
		return $this->hasOne(User::class);
	}

}
