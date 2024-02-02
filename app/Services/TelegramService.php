<?php

namespace App\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function requestHandler(Request $request)
    {

        // Parse the request URL
//        $url = parse_url($request->url());
//        dd();
        // Change the hostname to 'api.telegram.org'
        $telegramBaseUrl = 'api.telegram.org';

        // Reconstruct the modified URL
//        $query = $this->queryStringBuilder($request);
//        if ($query)
//        {
//           $query = "?".$query;
//        }
        $modifiedUrl = 'https://' . config('telegram.endpoint') . \Request::getRequestUri();
        $modifiedUrl = str_replace('api/telegram/', '', $modifiedUrl,);

        try {
            // Make a request to the modified URL
            $response = Http::get($modifiedUrl);

            // Return the Laravel HTTP response
//            return response($response->body(), $response->status());
            return response()->json(
                $response
            );
        } catch (\Exception $e) {
            // Handle errors, if any
            return response('Internal Server Error', 500)->header('content-type', 'text/plain');
        }
    }

    private function queryStringBuilder(Request $request)
    {
        $params = $request->all();
        return http_build_query($params);
    }

}
