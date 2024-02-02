<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    private $telegramService;
    public function __construct()
    {
        $this->telegramService = new TelegramService();
    }

    function handleRequest(Request $request)
    {

        return $this->telegramService->requestHandler($request);
    }
}
