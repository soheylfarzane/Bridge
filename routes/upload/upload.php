<?php

use Illuminate\Support\Facades\Route;


//مسیر های Unsplash
use App\Http\Controllers\UploadController;

Route::post('/upload-json', [UploadController::class, 'uploadJson']);

