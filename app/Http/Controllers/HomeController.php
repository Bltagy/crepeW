<?php

namespace App\Http\Controllers;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use GuzzleHttp\Client;

class HomeController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		// $this->middleware('auth');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		return view('home');
	}
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index2() {
		DriverManager::loadDriver(\BotMan\Drivers\Facebook\FacebookDriver::class);
		$config = ['facebook' => [
				  	'token' => 'EAAFZAkgzefrsBAIy5PlZCbk2p9i2L4wLOuVXHnVvefzJpWF7ybGMkEqIqJA1OA2vmBiSx4SPLzWrrsjiMWZCk37DDdNAcMQC4GnFcue4Yn5pMISIOZCpdMSVFkIQ0j5EPYf3HfbdSSs1C9ARQCfNIKKhLGBqjXnKgnYvYTgej9KEdeuZCk5ZAJ',
					'app_secret' => '594d197c44767222b979caa793922b1a',
				    'verification'=>'MY_SECRET_VERIFICATION_TOKEN',
				]];
		$botman = BotManFactory::create($config);
		// Give the bot something to listen for.
		$botman->hears('hello', function (BotMan $bot) {
		    $bot->reply('Hello yourself.');
		});

		// Start listening
		$botman->listen();
	}
}
