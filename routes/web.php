<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::group([
    'middleware' => 'setGlobals'
], function () {
  Route::view('/Global/Leaderboard', 'Global/leaderboard');
  Route::view('/Global/Stats', 'Global/stats');
  Route::view('/Global/Hero/Talent/Details', 'Global/Hero/Talent/details');
  Route::view('/Global/Hero/Talent/Builds', 'Global/Hero/Talent/builds');
});
