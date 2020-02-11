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
    Route::group(['prefix'=>'news'],function(){
          Route::get('/', 'NewsController@index')->name('news-list');
          Route::get('/detail/{newsId}', 'NewsController@edit')->name('news-detail');
          Route::post('/store', 'NewsController@store')->name('news-store');
          Route::post('/update', 'NewsController@update')->name('news-update');
          Route::delete('/delete/{news}', 'NewsController@destroy')->name('news-delete');
    });
  });
