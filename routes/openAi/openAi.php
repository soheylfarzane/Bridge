<?php

use App\Http\Controllers\ChatGPTController;
use Illuminate\Support\Facades\Route;



//مسیر های Unsplash
Route::controller(ChatGPTController::class)->group(function () {
    Route::post('/ask-gpt', 'askGPT');
    Route::post('/ask-image-gpt', 'askGPTWithImage');
});
