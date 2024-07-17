<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('login', 'ApiController@login');
Route::post('get', 'ProjectController@loadClient');
Route::get('zoom', 'ZoomMeetingController@getToken');
Route::get('comment', 'ProjectTaskController@commentStore');
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', 'ApiController@logout');
    Route::get('get-projects', 'ApiController@getProjects');
    Route::post('add-tracker', 'ApiController@addTracker');
    Route::post('stop-tracker', 'ApiController@stopTracker');
    Route::post('upload-photos', 'ApiController@uploadImage');
	Route::post('clock-in', 'ApiController@clockIn');
	Route::post('clock-out', 'ApiController@clockOut');
	Route::get('attendance-history', 'ApiController@attendanceHistory');
	Route::get('attendance-status', 'ApiController@getStatus');
	Route::get('profile/{id}', 'ApiController@getProfile');
	Route::get('attendance/today/{employeeId}', 'ApiController@getTodayAttendance');
	Route::post('refresh-token', 'ApiController@refreshToken');
	Route::get('approvals', 'ApiController@getApprovals');
    Route::get('leavetypes', 'ApiController@getLeaveTypes');
	Route::post('create-leave', 'ApiController@createLeave');
	Route::post('create-overtime', 'ApiController@createOvertime');
	Route::get('approvalsfinance', 'ApiController@getApprovalsFinance');
	Route::get('approvalsovertime', 'ApiController@getApprovalsOvertime');
	Route::get('getproject', 'ApiController@getProject');
	Route::get('getclient', 'ApiController@getClient');
	Route::get('getbranch', 'ApiController@getBranch');
	Route::post('getannouncement', 'ApiController@getAnnouncement');
	Route::post('reimbursment-types', 'ApiController@getReimbursmentTypes');
	Route::post('create-medical-allowance', 'ApiController@createMedical');
	Route::post('create-reimbursment', 'ApiController@createReimbursment');
	Route::post('create-absence', 'ApiController@createAbsence');
	Route::post('create-document-request', 'ApiController@createDocumentRequest');


	
});
