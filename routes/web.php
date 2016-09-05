<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'DashboardController@index');
Route::get('/connections/{connection}/check', 'ConnectionsController@check');

Route::resource('connections', 'ConnectionsController');
Route::resource('projects', 'ProjectsController');
Route::resource('deploys', 'DeploysController');

Auth::routes();
