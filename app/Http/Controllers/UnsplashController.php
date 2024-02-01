<?php

namespace App\Http\Controllers;

use App\Services\UnsplashService;
use Illuminate\Http\Request;

class UnsplashController extends Controller
{
    private $unsplashService;
    public function __construct()
    {
        $this->unsplashService = new UnsplashService();
    }

    public function searchPhotos(Request $request)
    {
        return $this->unsplashService->searchImages($request);
    }
}
