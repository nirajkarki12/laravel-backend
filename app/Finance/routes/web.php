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
  //Bank routes ../bank/*
	Route::group(['prefix'=> 'bank'], function(){
		Route::get('/', 'BankController@index')->name('banks');
		Route::get('/paginate', 'BankController@paginateBank')->name('banks-paginate');
		Route::get('/detail/{bankId}', 'BankController@detail')->name('banks-detail');
		Route::post('/store', 'BankController@store')->name('bank-store');
		Route::delete('/delete/{bank}', 'BankController@destroy')->name('bank-delete');
		Route::post('/update', 'BankController@update')->name('bank-update');
  });
//Network routes ../network/*
	Route::group(['prefix'=>'network'], function(){
    Route::get('/', 'NetworkController@index')->name('networks');
    Route::post('/store', 'NetworkController@store')->name('network-store');
    Route::get('/delete/{network}', 'NetworkController@destroy')->name('network-delete');
    Route::post('/update', 'NetworkController@update')->name('atm-update');
	});
//AtmPrice routes ../atm/*
	Route::group(['prefix'=>'atm'],function(){
    Route::get('/', 'AtmPriceController@index')->name('atms');
    Route::get('/detail/{atmPriceId}', 'AtmPriceController@detail')->name('atm-detail');
    Route::post('/store', 'AtmPriceController@store')->name('atm-store');
    Route::delete('/delete/{atmprice}', 'AtmPriceController@destroy')->name('atm-delete');
    Route::post('/update', 'AtmPriceController@update')->name('atm-update');
	});

});