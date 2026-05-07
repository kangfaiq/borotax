<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BillingController;
use App\Http\Controllers\Web\HistoryController;
use App\Http\Controllers\Web\SelfAssessmentController;
use App\Http\Controllers\Web\PublicMenuController;
use App\Http\Controllers\Web\AirTanahController;
use App\Http\Controllers\Web\ReklameController;
use App\Http\Controllers\BillingDocumentController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\SkpdReklameDocumentController;
use App\Http\Controllers\SkpdAirTanahDocumentController;
use App\Http\Controllers\StpdManualDocumentController;
use App\Http\Controllers\TaxAssessmentLetterDocumentController;
use App\Http\Controllers\ActivityLogFilePreviewController;
use App\Http\Controllers\HistoriPajakDocumentController;
use App\Http\Controllers\PortalMblbSubmissionAttachmentController;

use App\Http\Controllers\Web\PembetulanController;
use App\Http\Controllers\Web\PortalVerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC ROUTES
// =============================================

// Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Cek Billing (public)
Route::get('/cek-billing', [BillingController::class, 'check'])->name('billing.check');

// Histori Pajak per WP (public)
Route::get('/histori-pajak', function () {
    return view('portal.histori-pajak.index');
})->middleware('throttle:histori-pajak')->name('histori-pajak.index');
Route::get('/histori-pajak/pdf', [HistoriPajakDocumentController::class, 'showPdf'])
    ->middleware('throttle:histori-pajak')
    ->name('histori-pajak.pdf');

// Menu Publik
Route::get('/produk-hukum', [PublicMenuController::class, 'legalProducts'])->name('publik.produk-hukum');
Route::get('/kalkulator-sanksi', [PublicMenuController::class, 'penaltyCalculator'])->name('publik.kalkulator-sanksi');
Route::get('/kalkulator-air-tanah', [PublicMenuController::class, 'waterTaxCalculator'])->name('publik.kalkulator-air-tanah');
Route::get('/kalkulator-reklame', [PublicMenuController::class, 'reklameTaxCalculator'])->name('publik.kalkulator-reklame');
Route::get('/sewa-reklame', [PublicMenuController::class, 'sewaReklame'])->name('publik.sewa-reklame');
Route::get('/destinasi', [PublicMenuController::class, 'destinations'])->name('publik.destinasi');
Route::get('/destinasi/{destination:slug}', [PublicMenuController::class, 'destinationDetail'])->name('publik.destinasi.show');
Route::get('/berita', [PublicMenuController::class, 'newsList'])->name('publik.berita');
Route::get('/berita/{news:slug}', [PublicMenuController::class, 'newsDetail'])->name('publik.berita.show');

// =============================================
// SEWA REKLAME (Public — tanpa login)
// =============================================
Route::prefix('sewa-reklame')->name('sewa-reklame.')->group(function () {
    Route::get('/cek', [ReklameController::class, 'sewaCekTiket'])->name('cek');
    Route::get('/ajukan/{asetId}', [ReklameController::class, 'sewaForm'])->name('form');
    Route::post('/ajukan/{asetId}', [ReklameController::class, 'sewaStore'])->name('store');
    Route::get('/detail/{nomorTiket}', [ReklameController::class, 'sewaDetail'])->name('detail');
    Route::get('/edit/{nomorTiket}', [ReklameController::class, 'sewaEdit'])->name('edit');
    Route::put('/edit/{nomorTiket}', [ReklameController::class, 'sewaUpdate'])->name('update');
    Route::get('/skpd/{skpdId}/cetak', [SkpdReklameDocumentController::class, 'showPublic'])->name('skpd.cetak')->middleware('signed');
    Route::get('/skpd/{skpdId}/unduh', [SkpdReklameDocumentController::class, 'downloadPublic'])->name('skpd.unduh')->middleware('signed');
});

// =============================================
// AUTHENTICATION (Guest only)
// =============================================

Route::middleware('guest:portal')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('portal.login.submit');
    Route::get('/lupa-password', [WebAuthController::class, 'showForgotPasswordRequest'])->name('portal.password.forgot.form');
    Route::post('/lupa-password', [WebAuthController::class, 'requestPasswordResetOtp'])->name('portal.password.forgot.send');
    Route::get('/lupa-password/verifikasi', [WebAuthController::class, 'showForgotPasswordVerify'])->name('portal.password.forgot.verify');
    Route::post('/lupa-password/verifikasi', [WebAuthController::class, 'verifyPasswordResetOtp'])->name('portal.password.forgot.verify.submit');
    Route::post('/lupa-password/verifikasi/kirim-ulang', [WebAuthController::class, 'resendPasswordResetOtp'])->name('portal.password.forgot.resend');
    Route::get('/lupa-password/reset', [WebAuthController::class, 'showForgotPasswordReset'])->name('portal.password.forgot.reset');
    Route::post('/lupa-password/reset', [WebAuthController::class, 'resetForgotPassword'])->name('portal.password.forgot.reset.update');
});

// Logout (needs auth)
Route::post('/logout', [WebAuthController::class, 'logout'])
    ->middleware(['auth:portal', 'single.session'])
    ->name('portal.logout');

Route::middleware(['auth:web,portal', 'single.session'])
    ->get('/portal/pengajuan-mblb/{submissionId}/lampiran', PortalMblbSubmissionAttachmentController::class)
    ->name('portal.mblb-submissions.attachment');

if (app()->environment(['local', 'testing'])) {
    Route::middleware(['auth:web,portal', 'single.session'])->group(function () {
        Route::get('/document-previews', [DocumentPreviewController::class, 'index'])->name('document-previews.index');
        Route::get('/document-previews/{preview}', [DocumentPreviewController::class, 'show'])->name('document-previews.show');
    });
}

// =============================================
// AUTHENTICATED PORTAL (Wajib Pajak)
// =============================================

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware(['auth:portal', 'single.session'])->group(function () {
        Route::get('/password/change-first', [WebAuthController::class, 'showForceChangePassword'])
            ->name('force-password.form');
        Route::post('/password/change-first', [WebAuthController::class, 'updateForceChangePassword'])
            ->name('force-password.update');

        Route::middleware('password.changed')->group(function () {
            Route::get('/password/change', [WebAuthController::class, 'showChangePassword'])
                ->name('password.edit');
            Route::post('/password/change', [WebAuthController::class, 'updateChangePassword'])
                ->name('password.update');

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Transaction History
            Route::get('/riwayat', [HistoryController::class, 'transactions'])->name('history');

            // Cek Billing (portal — sidebar layout)
            Route::get('/cek-billing', [BillingController::class, 'portalCheck'])->name('billing');

            // Pengajuan MBLB
            Route::get('/pengajuan-mblb', [SelfAssessmentController::class, 'submissionIndex'])->name('mblb-submissions.index');
            Route::get('/pengajuan-mblb/{submissionId}', [SelfAssessmentController::class, 'submissionShow'])->name('mblb-submissions.show');
            Route::get('/pengajuan-mblb/{submissionId}/perbaiki', [SelfAssessmentController::class, 'submissionEdit'])->name('mblb-submissions.edit');
            Route::post('/pengajuan-mblb/{submissionId}/perbaiki', [SelfAssessmentController::class, 'submissionUpdate'])->name('mblb-submissions.update');

            // Self Assessment
            Route::get('/self-assessment', [SelfAssessmentController::class, 'index'])->name('self-assessment.index');
            Route::get('/self-assessment/{jenisPajakId}/create', [SelfAssessmentController::class, 'create'])->name('self-assessment.create');
            Route::post('/self-assessment', [SelfAssessmentController::class, 'store'])->name('self-assessment.store');
            Route::get('/self-assessment/{taxId}/success', [SelfAssessmentController::class, 'success'])->name('self-assessment.success');
            Route::get('/self-assessment/mblb-submissions/{submissionId}/success', [SelfAssessmentController::class, 'submissionSuccess'])->name('self-assessment.submission-success');

            // Pembetulan (Koreksi Billing)
            Route::get('/pembetulan', [PembetulanController::class, 'index'])->name('pembetulan.index');
            Route::get('/pembetulan/permohonan/{requestId}', [PembetulanController::class, 'show'])->name('pembetulan.show');
            Route::get('/pembetulan/{taxId}', [PembetulanController::class, 'create'])->name('pembetulan.create');
            Route::post('/pembetulan', [PembetulanController::class, 'store'])->name('pembetulan.store');

            // Owner-facing verification histories
            Route::get('/perubahan-data', [PortalVerificationController::class, 'dataChangeRequestIndex'])->name('data-change-requests.index');
            Route::get('/perubahan-data/{requestId}', [PortalVerificationController::class, 'dataChangeRequestShow'])->name('data-change-requests.show');
            Route::get('/stpd-manual', [PortalVerificationController::class, 'stpdManualIndex'])->name('stpd-manual.index');
            Route::get('/stpd-manual/{stpdId}', [PortalVerificationController::class, 'stpdManualShow'])->name('stpd-manual.show');
            Route::get('/gebyar', [PortalVerificationController::class, 'gebyarIndex'])->name('gebyar.index');
            Route::get('/gebyar/{submissionId}', [PortalVerificationController::class, 'gebyarShow'])->name('gebyar.show');

            // Air Tanah
            Route::get('/air-tanah', [AirTanahController::class, 'index'])->name('air-tanah.index');
            Route::get('/air-tanah/objek', [AirTanahController::class, 'objects'])->name('air-tanah.objects');
            Route::get('/air-tanah/skpd', [AirTanahController::class, 'skpdList'])->name('air-tanah.skpd-list');
            Route::get('/air-tanah/skpd/{skpdId}', [AirTanahController::class, 'skpdDetail'])->name('air-tanah.skpd-detail');

            // Reklame
            Route::get('/reklame', [ReklameController::class, 'index'])->name('reklame.index');
            Route::get('/reklame/objek', [ReklameController::class, 'objects'])->name('reklame.objects');
            Route::get('/reklame/objek/{objectId}', [ReklameController::class, 'objectDetail'])->name('reklame.object-detail');
            Route::get('/reklame/objek/{objectId}/perpanjangan', [ReklameController::class, 'requestExtension'])->name('reklame.request-extension');
            Route::post('/reklame/objek/{objectId}/perpanjangan', [ReklameController::class, 'storeExtension'])->name('reklame.store-extension');
            Route::get('/reklame/skpd', [ReklameController::class, 'skpdList'])->name('reklame.skpd-list');
            Route::get('/reklame/skpd/{skpdId}', [ReklameController::class, 'skpdDetail'])->name('reklame.skpd-detail');

            // Notifications (portal bell icon)
            Route::get('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        });
    });

    Route::middleware(['auth:web,portal', 'single.session'])->group(function () {
        Route::get('/billing/{taxId}/status', [BillingDocumentController::class, 'checkStatus'])->name('billing.check-status');
        Route::get('/billing/{taxId}/document', [BillingDocumentController::class, 'show'])->name('billing.document.show');
        Route::get('/billing/{taxId}/download', [BillingDocumentController::class, 'download'])->name('billing.document.download');
        Route::get('/billing/{taxId}/sptpd', [BillingDocumentController::class, 'downloadSptpd'])->name('sptpd.download');
        Route::get('/billing/{taxId}/sptpd/view', [BillingDocumentController::class, 'showSptpd'])->name('sptpd.show');
        Route::get('/billing/{taxId}/stpd', [BillingDocumentController::class, 'downloadStpd'])->name('stpd.download');
        Route::get('/billing/{taxId}/stpd/view', [BillingDocumentController::class, 'showStpd'])->name('stpd.show');
    });
});

// =============================================
// ADMIN: TOGGLE NAVIGATION MODE
// =============================================
Route::get('/admin/toggle-navigation', function () {
    $user = auth()->user();
    $user->navigation_mode = $user->usesTopNavigation() ? 'sidebar' : 'topbar';
    $user->save();

    return redirect()->back();
})->middleware(['auth:web', 'single.session'])->name('filament.toggle-navigation');

// =============================================
// SKPD REKLAME DOCUMENTS (auth, no portal prefix)
// =============================================
use App\Http\Controllers\PermohonanSewaFileController;
Route::middleware(['auth:web,portal', 'single.session'])->group(function () {
    Route::get('/skpd-reklame/{skpdId}/download', [SkpdReklameDocumentController::class, 'download'])->name('skpd-reklame.download');
    Route::get('/skpd-reklame/{skpdId}/view', [SkpdReklameDocumentController::class, 'show'])->name('skpd-reklame.show');

    // Serve permohonan sewa reklame files (KTP, NPWP, Desain)
    Route::get('/permohonan-sewa/{id}/file/{field}', PermohonanSewaFileController::class)->name('permohonan-sewa.file');
    Route::get('/activity-logs/{activityLog}/file-preview/{version}/{field}', ActivityLogFilePreviewController::class)->name('activity-logs.file-preview');
});

// =============================================
// SKPD AIR TANAH DOCUMENTS (auth, no portal prefix)
// =============================================
Route::middleware(['auth:web,portal', 'single.session'])->group(function () {
    Route::get('/skpd-air-tanah/{skpdId}/download', [SkpdAirTanahDocumentController::class, 'download'])->name('skpd-air-tanah.download');
    Route::get('/skpd-air-tanah/{skpdId}/view', [SkpdAirTanahDocumentController::class, 'show'])->name('skpd-air-tanah.show');
});

// =============================================
// SKRD SEWA TANAH DOCUMENTS (auth, no portal prefix)
// =============================================
use App\Http\Controllers\SkrdSewaDocumentController;
Route::middleware(['auth:web', 'single.session'])->group(function () {
    Route::get('/skrd-sewa/{skrdId}/download', [SkrdSewaDocumentController::class, 'download'])->name('skrd-sewa.download');
    Route::get('/skrd-sewa/{skrdId}/view', [SkrdSewaDocumentController::class, 'show'])->name('skrd-sewa.show');
});

// =============================================
// STPD MANUAL DOCUMENTS (auth, no portal prefix)
// =============================================
Route::middleware(['auth:web,portal', 'single.session'])->group(function () {
    Route::get('/stpd-manual/{stpdId}/download', [StpdManualDocumentController::class, 'download'])->name('stpd-manual.download');
    Route::get('/stpd-manual/{stpdId}/view', [StpdManualDocumentController::class, 'show'])->name('stpd-manual.show');

    Route::get('/surat-ketetapan/{letterId}/download', [TaxAssessmentLetterDocumentController::class, 'download'])->name('tax-assessment-letters.download');
    Route::get('/surat-ketetapan/{letterId}/view', [TaxAssessmentLetterDocumentController::class, 'show'])->name('tax-assessment-letters.show');
});


