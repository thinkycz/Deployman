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
Route::get('/projects/{project}/check', 'ProjectsController@check');
Route::get('/projects/{project}/deploy', 'ProjectsController@deploy');
Route::get('/projects/{project}/cleanup', 'ProjectsController@cleanup');
Route::get('/deploys/{deploy}/fire', 'DeploysController@fire');
Route::get('/deploys/{deploy}/status', 'DeploysController@status');

Route::resource('connections', 'ConnectionsController');
Route::resource('projects', 'ProjectsController');
Route::resource('deploys', 'DeploysController');

Auth::routes();
