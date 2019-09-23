<?php

namespace App\Providers;
use App\HomeData;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $amount = HomeData::where('name', 'discount')->first()->value;
        if ( $amount !=0 ){
        	$date = new \Carbon\Carbon;
        	$valid_until = HomeData::where('name', 'valid_until')->first()->value;
			if ($date > $valid_until) {
				HomeData::where('name', 'discount')->update(['value' => 0]);
				HomeData::where('name', 'valid_until')->update(['value' => null]);
			}
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        

        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
    }
}
