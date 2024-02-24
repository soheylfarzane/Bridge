<?php

namespace App\Services;

use App\Jobs\CleanerJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;



class UnsplashService
{
    private $endpoint;
    private $searchPath;
    public function __construct()
    {
        $this->endpoint = "https://".config("unsplash.endpoint");
        $this->searchPath = config("unsplash.paths.search");

    }
    private function queryStringBuilder(Request $request)
    {
        $params = $request->all();
        return http_build_query($params);
    }

    public function searchImages(Request $request)
    {
        $query = $this->queryStringBuilder($request);
        return  Http::get($this->endpoint.$this->searchPath."?$query");
    }

    public function downloadImage($id, Request $request)
    {
        $ixid = $request["ixid"];
        $ixlib = $request["ixlib"];

        // Assuming you have the image URL
        $imageUrl = "https://images.unsplash.com/$id?ixid=$ixid&ixlib=$ixlib";

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;
        $path = 'stock/images' . '/' . $year . '/' . $month . '/' . $day . '/';
        $filename = 'image_' . time() . '.jpg';
        $directory = public_path($path);
        $photo = $path . $filename;

        // Initialize cURL session
        $ch = curl_init($imageUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL verification

        // Execute cURL session and get image content
        $imageContent = curl_exec($ch);

        // Check for cURL errors
        if(curl_errno($ch)){
            // Handle error (you may want to log or return an error response)
            curl_close($ch);
            return response()->json(["error" => "Failed to download image"]);
        }

        // Close cURL session
        curl_close($ch);

        // Generate a unique filename
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Save the image to the public storage directory
        file_put_contents(public_path($path . $filename), $imageContent);

        $path = config("app.url") . "/" . $photo;
        CleanerJob::dispatch($path)->delay(40);

        return response()->json([
            "url" => $path
        ]);
    }


}
