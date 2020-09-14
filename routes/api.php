<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('labview/register','Api\LoginController@register');
Route::post('labview/login','Api\LoginController@login');
Route::get('labview/test','Api\LoginController@test');

Route::any('labview/userlist','Api\UserController@userlist');
Route::post('labview/adduser','Api\UserController@adduser');
Route::post('labview/updateuser','Api\UserController@updateuser');
Route::post('labview/deleteuser','Api\UserController@deleteuser');
Route::post('labview/import','Api\UserController@import');
Route::post('labview/importeacher','Api\UserController@importeacher');


Route::group(['prefix'=>'labview','middleware'=>'check.login'],function (){
    Route::post('basis','Api\ScoreController@basis');
});
//Route::post('labview/basis','Api\ScoreController@basis');
