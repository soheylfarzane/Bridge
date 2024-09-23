<?php

use App\Http\Controllers\GoogleVisionController;
use Illuminate\Support\Facades\Route;


//مسیر های Unsplash
Route::controller(GoogleVisionController::class)->group(function () {
    Route::post('/analyze-image', 'analyzeImage');
});
