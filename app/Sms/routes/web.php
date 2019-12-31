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

Route::group(['prefix' => 'v1/sms', 'middleware' => ['jwt.verify']], function() {
		Route::post('/', 'SmsController@index')->name('sms-log-list');
		Route::post('/resend', 'SmsController@resendSms')->name('resend-sms');
});
