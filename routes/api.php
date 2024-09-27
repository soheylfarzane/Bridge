<?php

use App\Http\Controllers\TelegramController as TelegramControllerAlias;
use App\Http\Controllers\UnsplashController as UnsplashControllerAlias;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//مسیر های Unsplash
Route::controller(UnsplashControllerAlias::class)->group(function () {
    Route::get('/search/photos', 'searchPhotos');
    Route::get('photo/{id}', 'downloadImage');

});

Route::controller(TelegramControllerAlias::class)->prefix("telegram")->group(function () {
    Route::post('/{any?}/{anyl?}/{anjy?}', 'handleRequest');
});

require __DIR__.'/googleCloudVision/googleCloudVision.php';
require __DIR__.'/upload/upload.php';
require __DIR__.'/openAi/openAi.php';
