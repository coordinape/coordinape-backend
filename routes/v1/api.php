<?php

use App\Http\Controllers\CircleController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\EpochController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('{circle_id}')->group(function () {
    Route::prefix('admin')->middleware(['verify-admin-sign'])->group(function () {
        Route::put('/circles/{circle}', [CircleController::class, 'updateCircle']);
        Route::put('/users/{address}', [UserController::class, 'adminUpdateUser']);
        Route::post('/users', [UserController::class, 'createUser']);
        Route::delete('/users/{address}', [UserController::class, 'deleteUser']);
        Route::post('/v2/epoches', [EpochController::class, 'createEpoch']);
        Route::put('/epoches/{epoch}', [EpochController::class, 'updateEpoch']);
        Route::delete('/epoches/{epoch}', [EpochController::class, 'deleteEpoch']);
        Route::post('/upload-logo', [CircleController::class, 'uploadCircleLogo']);
        Route::get('/webhook', [CircleController::class, 'getWebhook']);

    });
    Route::middleware(['verify-sign'])->group(function () {
        Route::put('/users', [UserController::class, 'updateMyUser']);
        Route::put('/users/{address}', [UserController::class, 'updateMyUser']); // deprecated
        Route::post('/v2/token-gifts/{address}', [DataController::class, 'newUpdateGifts']);
        Route::post('/teammates', [DataController::class, 'updateTeammates']);
        Route::post('/nominees', [NominationController::class, 'createNominee']);
        Route::post('/vouch', [NominationController::class, 'addVouch']);
    });

    Route::get('/circles', [CircleController::class, 'getCircles']);
    Route::get('/pending-token-gifts', [DataController::class, 'getPendingGifts']);
    Route::get('/csv', [DataController::class, 'generateCsv']);
    Route::get('/nominees', [NominationController::class, 'getNominees']);
    Route::get('/epoches', [EpochController::class, 'epoches']);

});

Route::middleware(['verify-sign'])->group(function () {
    Route::post('/upload-avatar/{address}', [ProfileController::class, 'uploadProfileAvatar']);
    Route::post('/upload-background/{address}', [ProfileController::class, 'uploadProfileBackground']);
    Route::post('/profile', [ProfileController::class, 'updateMyProfile']);
});

Route::get('/profile/{address}', [ProfileController::class, 'getProfile']);
Route::get('/protocols', [DataController::class, 'getProtocols']);
Route::get('/circles', [CircleController::class, 'getCircles']);
Route::middleware(['hcaptcha-verify', 'verify-sign'])->group(function () {
    Route::post('/circles', [CircleController::class, 'createCircle']);
});
Route::get('/users/{address}', [UserController::class, 'getUser']);
Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/token-gifts', [DataController::class, 'getGifts']);
Route::get('/pending-token-gifts', [DataController::class, 'getPendingGifts']);
Route::get('/active-epochs', [EpochController::class, 'getActiveEpochs']);
