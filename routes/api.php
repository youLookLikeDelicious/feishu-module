<?php

use Illuminate\Support\Facades\Route;
use Modules\Feishu\Http\Controllers\FeishuController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware([])->prefix('v1')->group(function () {
    Route::post('/doc/sync', [FeishuController::class, 'syncDoc'])->name('feishu.doc.sync');
});
