<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{

    function handleRequest($request)
    {
        // Parse the request URL
        $url = parse_url($request->fullUrl());

        // Change the hostname to 'api.telegram.org'
        $url['host'] = 'api.telegram.org';

        // Reconstruct the modified URL
        $modifiedUrl = $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . $url['query'];

        try {
            // Make a request to the modified URL
            $response = Http::get($modifiedUrl);

            // Return the Laravel HTTP response
            return response($response->body(), $response->status());
        } catch (\Exception $e) {
            // Handle errors, if any
            return response('Internal Server Error', 500)->header('content-type', 'text/plain');
        }
    }
}
