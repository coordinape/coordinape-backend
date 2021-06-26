<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;
use App\Http\Controllers\BotController;
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

//Route::domain('{subdomain}.'.env('APP_DOMAIN'))->group(function () {
//
//    Route::get('/circles', [DataController::class, 'getCircles']);
//    Route::get('/users/{address}', [DataController::class, 'getUser2']);
//    Route::get('/users', [DataController::class, 'getUsers']);
//    Route::put('/users/{address}', [DataController::class, 'updateUser2']);
//
//    Route::get('/pending-token-gifts', [DataController::class, 'getPendingGifts']);
//    Route::get('/token-gifts', [DataController::class, 'getGifts']);
//    Route::post('/token-gifts/{address}', [DataController::class, 'updateGifts2']);
//    Route::post('/teammates', [DataController::class, 'updateTeammates']);
//    Route::post('/upload', [DataController::class, 'uploadAvatar']);
//    Route::get('/csv', [DataController::class, 'generateCsv']);
//    Route::get('/epoches',[DataController::class, 'epoches']);
//    Route::prefix('admin')->group(function () {
//        Route::put('/circles/{circle}', [DataController::class, 'updateCircle']);
//        Route::put('/users/{address}', [DataController::class, 'adminUpdateUser']);
//        Route::post('/users', [DataController::class, 'createUser']);
//    });
//});

Route::prefix('{subdomain}')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::put('/circles/{circle}', [DataController::class, 'updateCircle']);
        Route::put('/users/{address}', [DataController::class, 'adminUpdateUser']);
        Route::post('/users', [DataController::class, 'createUser']);
        Route::post('/epoches', [DataController::class, 'createEpoch']);
        Route::delete('/epoches/{epoch}', [DataController::class, 'deleteEpoch']);
        Route::delete('/users/{address}', [DataController::class, 'deleteUser']);
    });
    Route::get('/circles', [DataController::class, 'getCircles']);
    Route::get('/users/{address}', [DataController::class, 'getUser2']);
    Route::get('/users', [DataController::class, 'getUsers']);
    Route::put('/users/{address}', [DataController::class, 'updateUser']);

    Route::get('/pending-token-gifts', [DataController::class, 'getPendingGifts']);
    Route::get('/token-gifts', [DataController::class, 'getGifts']);
    Route::post('/token-gifts/{address}', [DataController::class, 'updateGifts']);
    Route::post('/teammates', [DataController::class, 'updateTeammates']);
    Route::post('/upload', [DataController::class, 'uploadAvatar']);
    Route::get('/csv', [DataController::class, 'generateCsv']);
    Route::get('/epoches',[DataController::class, 'epoches']);
    Route::get('/burns', [DataController::class, 'burns']);
});


Route::get('/protocols', [DataController::class, 'getProtocols']);
Route::get('/circles', [DataController::class, 'getCircles']);
//// not used for now
//Route::post('/circles', [DataController::class, 'createCircle']);
//Route::put('/circles/{circle}', [DataController::class, 'updateCircle']);
////

Route::get('/users/{address}', [DataController::class, 'getUser']);
Route::get('/users', [DataController::class, 'getUsers']);
Route::get('/token-gifts', [DataController::class, 'getGifts']);
Route::get('/pending-token-gifts', [DataController::class, 'getPendingGifts']);
Route::get('/active-epochs',[DataController::class, 'getActiveEpochs']);
// phased out
//Route::get('/pending-token-gifts', [DataController::class, 'getPendingGifts']);
//Route::post('/teammates', [DataController::class, 'updateTeammates']);
//Route::post('/upload', [DataController::class, 'uploadAvatar']);
//Route::get('/csv', [DataController::class, 'generateCsv']);
//Route::post('/token-gifts/{address}', [DataController::class, 'updateGifts']);
// phased out


Route::post("/".env('TELEGRAM_BOT_TOKEN')."/bot-update", [BotController::class,'webHook']);


