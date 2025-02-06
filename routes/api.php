<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProbeController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\LifeCycleController;
use Illuminate\Support\Facades\Route;
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

Route::prefix('widget')->group(function () {
    Route::post('/{id}/upload-cover', [ClientController::class, 'uploadCustomCover']);
    Route::post('/{id}/order', [OrderController::class, 'createOrder'])->whereUuid('id');
    Route::get('/{id}/preview', [ClientController::class, 'previewCertificatePdf'])->whereUuid('id');
    Route::get('/{id}/products', [ClientController::class, 'getProducts'])->whereUuid('id');
    Route::post('/order/xls', [ClientController::class, 'parseFromXlsx']);
    Route::get('/{term}', [ClientController::class, 'getWidgetInfo']);
    Route::get('/', [ClientController::class, 'getWidgetInfo']);

    Route::post('/{id}/promo-code', [ClientController::class, 'promoProducts'])->whereUuid('id');
});

Route::post('order/{id}/reorder', [OrderController::class, 'resendOrderToVendor'])->whereUuid('id')->middleware('verifyHash');

Route::post('/{id}/support', [SupportController::class, 'sendSupport'])->whereUuid('id');

Route::get('/{widget_id}/view/{id}', [ClientController::class, 'showCertificate'])->whereUuid('widget_id')->whereUuid('id');
Route::get('/certificate/pdf/{id}', [ClientController::class, 'showPdf'])->whereUuid('id');

Route::post('/certificate/pin/{id}', [ClientController::class, 'sendPinSms'])->whereUuid('id');
Route::post('/certificate/info', [ClientController::class, 'getCftCertificateInfo'])->whereUuid('id');
Route::any('/callback/yookassa', [OrderController::class, 'callbackYookassa']);
Route::any('/test/yookassa', [ClientController::class, 'getPaymentStatus']);

Route::get('/health', [ProbeController::class, 'health']);
Route::get('/read', [ProbeController::class, 'read']);
Route::get('/version', [ProbeController::class, 'version']);
Route::get('/api/prob/cache', [ProbeController::class, 'cache']);
Route::get('/api/prob/queues', [ProbeController::class, 'queues']);

// жизненный цикл заказа
//Route::post('/life_cycle', [LifeCycleController::class, 'createStatus']);
Route::post('/api/life_cycle', [LifeCycleController::class, 'createStatus']);
Route::get('/api/life_cycle/history', [LifeCycleController::class, 'getHistory']);
Route::get('/api/life_cycle/all', [LifeCycleController::class, 'getAll']);

