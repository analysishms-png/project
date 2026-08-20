<?php

use App\Http\Controllers\Api\ChequeDesignController;
use App\Http\Controllers\Api\CompanyInfo;
use App\Http\Controllers\Api\InhouseRoomGet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reservation;
use App\Http\Controllers\ApiInhouseRoomGet;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Essl\EsslWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/reservation/push/{api_key}', [Reservation::class, 'pushReservation']);
Route::post('/essl/webhook/attendance', [EsslWebhookController::class, 'attendance']);
Route::middleware(['api.auth'])->group(function () {
    // Get Company Info
    Route::get('companyinfo/{api_key}', [CompanyInfo::class, 'companyinfoget']);
    // Inhouse Booked Room Get
    Route::get('bookedrooms/{api_key}', [InhouseRoomGet::class, 'bookedrooms']);
    // Inhouse Reserved Room Get
    Route::get('reservedrooms/{api_key}', [InhouseRoomGet::class, 'reservedrooms']);
});

Route::post('/reactlogin', [LoginController::class, 'reactlogin']);
Route::post('/reactlogout', [LoginController::class, 'reactlogout']);
// Load Cheque Designs
Route::get('/cheque-designs', [ChequeDesignController::class, 'index']);
// Get Single Cheque Design
Route::get('/cheque-designs/{id}', [ChequeDesignController::class, 'show']);
// Save Cheque Design
Route::post('/cheque-designs',[ChequeDesignController::class, 'store']);
// Update Cheque Design
Route::put('/cheque-designs/{id}', [ChequeDesignController::class, 'update']);
// Delete Cheque Design
Route::delete('/cheque-designs/{id}', [ChequeDesignController::class, 'destroy']);