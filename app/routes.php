<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::get('/', function() {
    return View::make('pages/home');
});
Route::post('uploadImage', 'ImageController@process');
Route::get('uploadImage', 'ImageController@index');
Route::get('downloadImage/{image}', 'ImageController@download');
Route::delete('deleteImage/{image}', 'ImageController@delete');
