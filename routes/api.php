<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\CircleController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\EpochController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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

/********************************** V2 ENDPOINTs ***************************************************/

// login
Route::post('/v2/login', [ProfileController::class, 'login'])->middleware(['verify-login-sign']);

/************************* TOKEN GATED endpoints *********************************/

Route::prefix('v2')->middleware(['auth:sanctum'])->group(function () {

    Route::prefix('{circle_id}')->group(function () {

        /************************* ADMIN TOKEN ENDPOINTS ****************************/
        Route::prefix('admin')->middleware(['verify-circle-admin'])->group(function () {
            Route::put('/circles/{circle}', [CircleController::class, 'updateCircle']);
            Route::put('/users/{address}', [UserController::class, 'adminUpdateUser']);
            Route::post('/users', [UserController::class, 'createUser']);
            Route::delete('/users/{address}', [UserController::class, 'deleteUser']);
            Route::post('/epoches', [EpochController::class, 'createEpoch']);
            Route::put('/epoches/{epoch}', [EpochController::class, 'updateEpoch']);
            Route::delete('/epoches/{epoch}', [EpochController::class, 'deleteEpoch']);
            Route::post('/upload-logo', [CircleController::class, 'uploadCircleLogo']);
            Route::get('/webhook', [CircleController::class, 'getWebhook']);
            Route::post('/bulk-update', [UserController::class, 'bulkUpdate']);
            Route::post('/bulk-create', [UserController::class, 'bulkCreate']);
            Route::post('/bulk-delete', [UserController::class, 'bulkDelete']);
            Route::post('/bulk-restore', [UserController::class, 'bulkRestore']);

        });
        /************************* ADMIN TOKEN ENDPOINTS ****************************/

        Route::middleware(['verify-circle-user'])->group(function () {
            Route::put('/users', [UserController::class, 'updateMyUser']);
            Route::post('/token-gifts', [DataController::class, 'newUpdateGifts']);
            Route::post('/teammates', [DataController::class, 'updateTeammates']);
            Route::post('/nominees', [NominationController::class, 'createNominee']);
            Route::post('/vouch', [NominationController::class, 'addVouch']);
            Route::get('/nominees', [NominationController::class, 'getNominees']);
            Route::get('/csv', [DataController::class, 'generateCsv']);
        });

        Route::get('/epoches', [EpochController::class, 'epoches']);

    });

    Route::post('/circles', [CircleController::class, 'createCircle']);
    Route::get('/manifest', [ProfileController::class, 'manifest']);
    Route::post('/logout', [ProfileController::class, 'logout']);
    Route::get('/full-circle', [CircleController::class, 'fullCircleData']);
    Route::get('/token-gifts', [DataController::class, 'newGetGifts']);
    Route::get('/pending-token-gifts', [DataController::class, 'newGetPendingGifts']);
    Route::post('/upload-avatar', [ProfileController::class, 'uploadMyProfileAvatar']);
    Route::post('/upload-background', [ProfileController::class, 'uploadMyProfileBackground']);
    Route::post('/profile', [ProfileController::class, 'updateMyProfile']);
    Route::get('/profile/{address}', [ProfileController::class, 'getProfile']);
    Route::get('/circles', [CircleController::class, 'getCircles']);
    Route::get('/protocols', [DataController::class, 'getProtocols']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::get('/active-epochs', [EpochController::class, 'getActiveEpochs']);

});

/************************* TOKEN GATED endpoints *********************************/

/************************* EXTERNAL USED endpoints *********************************/

Route::prefix('{circle_id}')->group(function () {
    Route::get('/token-gifts', [DataController::class, 'getGiftsWithoutNotes']);
    //temp fix
    Route::get('/csv', [DataController::class, 'generateCsv']);
});
/************************* EXTERNAL USED endpoints *********************************/


Route::post("/" . config('telegram.token') . "/bot-update", [BotController::class, 'webHook']);

/********************************** V2 ENDPOINTs ***************************************************/


Route::fallback(function () {
    return response()->json(['message' => 'Endpoint Not Found'], 404);
})->name('api.fallback.404');

