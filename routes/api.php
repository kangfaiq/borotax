<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OtpController;

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

// Public NPWPD Lookup (no auth)
Route::get('cek-npwpd/{npwpd}', function (string $npwpd) {
    $wp = \App\Domain\WajibPajak\Models\WajibPajak::where('npwpd', $npwpd)
        ->where('status', 'disetujui')
        ->first();

    if (! $wp) {
        return response()->json(['message' => 'NPWPD tidak ditemukan'], 404);
    }

    return response()->json(['nama' => $wp->nama_lengkap]);
});

Route::prefix('v1')->group(function () {
    // OTP Verification (Public)
    Route::post('auth/request-otp', [OtpController::class, 'requestOtp']);
    Route::post('auth/verify-otp', [OtpController::class, 'verifyOtp']);
    Route::post('auth/resend-otp', [OtpController::class, 'resendOtp']);

    // Auth Public
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Master Data (Public)
    Route::get('master/provinces', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'getProvinces']);
    Route::get('master/regencies', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'getRegencies']);
    Route::get('master/districts', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'getDistricts']);
    Route::get('master/villages/{district}', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'getVillages']);
    Route::get('master/tax-types', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'getTaxTypes']);

    // Public Info
    Route::get('news', [\App\Http\Controllers\Api\V1\PublicController::class, 'getNews']);
    Route::get('destinations', [\App\Http\Controllers\Api\V1\PublicController::class, 'getDestinations']);
    Route::get('billing/check', [\App\Http\Controllers\Api\V1\TransactionController::class, 'checkBilling']); // Check Billing Public

    // Auth Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('update-password', [AuthController::class, 'updatePassword']);

        Route::middleware('password.changed')->group(function () {
            Route::post('update-profile', [AuthController::class, 'updateProfile']);
            Route::post('update-pin', [AuthController::class, 'updatePin']);
            Route::post('verify-pin', [AuthController::class, 'verifyPin']);

            // Water Tax
            Route::get('water-objects', [\App\Http\Controllers\Api\V1\WaterTaxController::class, 'getObjects']);
            Route::post('water-objects', [\App\Http\Controllers\Api\V1\WaterTaxController::class, 'registerObject']);
            Route::post('water-reports', [\App\Http\Controllers\Api\V1\WaterTaxController::class, 'submitReport']);
            Route::get('water-reports/history', [\App\Http\Controllers\Api\V1\WaterTaxController::class, 'getHistory']);

            // Reklame
            Route::get('reklame-objects', [\App\Http\Controllers\Api\V1\ReklameController::class, 'getObjects']);
            Route::post('reklame-extensions', [\App\Http\Controllers\Api\V1\ReklameController::class, 'submitExtension']);
            Route::get('reklame-requests', [\App\Http\Controllers\Api\V1\ReklameController::class, 'getRequests']);

            // Sewa Reklame Aset Pemkab
            Route::get('reklame-aset-pemkab', [\App\Http\Controllers\Api\V1\ReklameController::class, 'getAsetPemkab']);
            Route::post('reklame-sewa', [\App\Http\Controllers\Api\V1\ReklameController::class, 'submitSewa']);
            Route::get('reklame-sewa', [\App\Http\Controllers\Api\V1\ReklameController::class, 'getSewaList']);

            // Transactions / Self Assessment
            Route::post('taxes/self-assessment', [\App\Http\Controllers\Api\V1\TransactionController::class, 'createSelfAssessment']);
            Route::get('taxes/history', [\App\Http\Controllers\Api\V1\TransactionController::class, 'getTransactions']);

            // Gebyar Sadar Pajak
            Route::post('gebyar/submit', [\App\Http\Controllers\Api\V1\GebyarController::class, 'submit']);
            Route::get('gebyar/history', [\App\Http\Controllers\Api\V1\GebyarController::class, 'getHistory']);

            // Notifications
            Route::get('notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
            Route::get('notifications/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
            Route::post('notifications/{id}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
            Route::post('notifications/read-all', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);
        });
    });
});
