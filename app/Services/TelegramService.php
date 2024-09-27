<?php

namespace App\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function requestHandler(Request $request)
    {

        $modifiedUrl = 'https://' . config('telegram.endpoint') . \Request::getRequestUri();
        $modifiedUrl = str_replace('api/telegram/', '', $modifiedUrl,);

        try {
            // Make a request to the modified URL
            $response = Http::get($modifiedUrl);

            // Return the Laravel HTTP response
//            return response($response->body(), $response->status());
            return response($response);
        } catch (\Exception $e) {
            // Handle errors, if any
            return response('Internal Server Error', 500)->header('content-type', 'text/plain');
        }
    }
    public function postRequestHandler(Request $request)
    {
        // Modified URL برای ارسال به تلگرام
        $modifiedUrl = 'https://' . config('telegram.endpoint') . \Request::getRequestUri();
        $modifiedUrl = str_replace('api/telegram/', '', $modifiedUrl);

        try {
            // دریافت داده‌های ارسالی از درخواست
            $postData = $request->all(); // Get all request data

            // ارسال درخواست به تلگرام همراه با داده‌های درخواست
            $response = Http::post($modifiedUrl, $postData);

            // پاسخ HTTP لاراول بر اساس نتیجه درخواست
            return response($response->body(), $response->status());
        } catch (\Exception $e) {
            // مدیریت خطاها
            return response('Internal Server Error: ' . $e->getMessage(), 500)->header('content-type', 'text/plain');
        }
    }


    private function queryStringBuilder(Request $request)
    {
        $params = $request->all();
        return http_build_query($params);
    }

}
