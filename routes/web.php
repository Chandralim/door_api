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

Route::post('/admin/login', 'AdminAccount@login');
Route::post('/admin/refresh', 'AdminAccount@refresh');

Route::get('/room_activity','IOT\RoomActivityController@store');
Route::get('/get_room_activity','IOT\RoomActivityController@get');

Route::middleware('auth:internal')->group(function () {
  Route::post('/admin/logout', 'AdminAccount@logout');
  Route::get('/admin/getInfo', 'AdminAccount@getInfo');
  Route::put('/admin/change_password','AdminAccount@change_password');
  Route::put('/admin/change_fullname','AdminAccount@change_fullname');

  Route::get('/users','AdminController@index');
  Route::get('/user','AdminController@show');
  Route::post('/user','AdminController@store');
  Route::put('/user','AdminController@update');

  Route::get('/rooms','RoomController@index');
  Route::get('/rooms/download','RoomController@download');
  Route::get('/room','RoomController@show');
  Route::post('/room','RoomController@store');
  Route::put('/room','RoomController@update');


  Route::get('/dashboards','DashboardController@index');
  Route::get('/dashboard/room_histories','DashboardController@getDataByRoom');
});


Route::get('/', function () {
    return '404 Not Found';
});