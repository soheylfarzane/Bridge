<?php

namespace App\Services;

use Illuminate\Http\Request;
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

}
