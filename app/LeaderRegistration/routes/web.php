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
Route::group(['prefix' => 'v1', 'middleware' => ['jwt.verify']], function() {
  Route::group(['prefix'=>'leader'],function(){
		Route::post('/', 'LeaderRegistrationController@index')->name('leader-list');
		Route::get('/detail/{leaderId}', 'LeaderRegistrationController@edit')->name('leader-detail');
		Route::post('/store', 'LeaderRegistrationController@store')->name('leader-store');
		Route::post('/update', 'LeaderRegistrationController@update')->name('leader-update');
		Route::get('/unpaid-leaders', 'LeaderRegistrationController@unpaidLeaders')->name('unpaid-leaders');
  });
});
