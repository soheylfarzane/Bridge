<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

    public function downloadImage($id,Request $request)
    {
        $ixid =$request["ixid"];
        $ixlib =$request["ixlib"];
        // Assuming you have the image URL
        $imageUrl = "https://images.unsplash.com/$id?ixid=$ixid&ixlib=$ixlib";

// Make a request to the image URL
        $response = Http::get($imageUrl);

// Check if the request was successful (status code 200)
        if ($response->successful()) {
            // Generate a unique filename for the downloaded image
            $filename = uniqid('image_') . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);

            // Save the downloaded image to the storage path (public disk in this example)
            Storage::disk('public')->put($filename, $response->body());

            // Get the public URL for the saved image
            $savedImageUrl = Storage::url($filename);

            // Output the URL of the saved image
            return $savedImageUrl;
        } else {
            // Handle the case where the image download failed
            return 'Failed to download the image.';
        }
    }

}
