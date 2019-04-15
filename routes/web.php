<?php

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


Route::get('/test','TestController@valid');
Route::post('/test','TestController@wxEven');

Route::get('/test/token','TestController@getAccesstoken');
Route::get('/test/getUserinfo','TestController@getUserinfo');
Route::get('/test/getMenu','TestController@getMenu');
Route::get('/test/getAccesstoken','TestController@getAccesstoken');
