<?php

namespace App\Services;

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

    public function downloadImage($id,Request $request)
    {
        $ixid =$request["ixid"];
        $ixlib =$request["ixlib"];
        // Assuming you have the image URL
        $imageUrl = "https://images.unsplash.com/$id?ixid=$ixid&ixlib=$ixlib";

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;
        $path = 'stock/images'.'/' . $year . '/' . $month . '/' . $day . '/';
        $filename = 'image_' . time() . '.jpg';
        $directory = public_path($path);
        $photo = $path.$filename;

        // Get image content
        $imageContent = file_get_contents($imageUrl);
        // Generate a unique filename
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        // Save the image to the public storage directory
        file_put_contents(public_path($path.$filename), $imageContent);

        $path = env('APP_URL') ."/".$photo;
        return response()->json(
            [
                "url" =>$path
            ]
        );
    }

}
