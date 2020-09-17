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




Route::group(['prefix'=>'labview','middleware'=>'check.login'],function (){
    Route::get('userlist','Api\UserController@userlist');
    Route::post('adduser','Api\UserController@adduser');
    Route::post('updateuser','Api\UserController@updateuser');
    Route::post('deleteuser','Api\UserController@deleteuser');
    Route::post('import','Api\UserController@import');
    Route::post('importeacher','Api\UserController@importeacher');
    Route::post('basis','Api\ScoreController@basis');
    Route::any('lookbasis','Api\ScoreController@lookbasis');
});
//Route::post('labview/basis','Api\ScoreController@basis');
