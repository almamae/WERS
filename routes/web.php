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

Route::get('/', 'HomeController@index')->middleware('auth')->name('home');


Route::get('userContact', 'UserController@userContact');  

// Route::get('/', function () {
//     return view('responder');
// });

Auth::routes();

Route::get('users/{user}',  ['as' => 'edit.user', 'uses' => 'UserController@edit'])->middleware('auth');
Route::put('users/{user}/update', 'UserController@update')->middleware('auth');


Route::get('/signin',function(){
	return view('auth/login');
});


Route::get('/user-reports', 'ReportPersonalController@userReports');
Route::get('/user-reports/user-info/{id}', 'ReportPersonalController@getUserInfo');
Route::post('/user-reports/validate/{id}/{userid}/{resolve?}', 'ReportPersonalController@validate');
Route::post('/user-reports/reject/{id}/{userid}', 'ReportPersonalController@reject');


Route::get('/stats', 'ReportPersonalController@stats');
Route::get('/notifications/unread', 'NotificationController@getUnread');
Route::post('/notifications/read/{id}', 'NotificationController@read');
Route::get('/notifications/unread/count', 'NotificationController@getUnreadCount');

Route::get('user-reports/view/{rep}',  ['as' => 'reports.status', 'uses' => 'Api\ReportController@trackStatus'])->middleware('auth');
