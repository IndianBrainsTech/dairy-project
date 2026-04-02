<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

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

Route::get('/testing', function (Request $request) {
    return 'Testing done';
});

Route::get('/register', [ApiController::class, 'register']);
Route::get('/verify-otp', [ApiController::class, 'verifyOtp']);
Route::post('/login', [ApiController::class, 'login']);
Route::get('/fetch-data-enquiry', [ApiController::class, 'fetchDataEnquiry']);
Route::post('/new-enquiry', [ApiController::class, 'newEnquiry']);
Route::post('/save-enquiry', [ApiController::class, 'saveEnquiry']);
Route::post('/store-enquiry', [ApiController::class, 'storeEnquiry']);
Route::post('/upload-photo', [ApiController::class, 'uploadPhoto']);
Route::get('/followups', [ApiController::class, 'followups']);
Route::get('/followup-history', [ApiController::class, 'followupHistory']);
Route::post('/add-followup', [ApiController::class, 'addFollowup']);
Route::get('/reset-password', [ApiController::class, 'resetPassword']);
Route::get('/products', [ApiController::class, 'products']);
Route::get('/units', [ApiController::class, 'units']);
Route::get('/areas', [ApiController::class, 'areas']);
Route::get('/shops', [ApiController::class, 'shops']);
Route::post('/mark-attendance', [ApiController::class, 'markAttendance']);
Route::get('/location-update', [ApiController::class, 'updateLocation']);
Route::post('/place-order', [ApiController::class, 'placeOrder']);
Route::get('/list-orders', [ApiController::class, 'listOrders']);
Route::get('/view-order', [ApiController::class, 'viewOrder']);