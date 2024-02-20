<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\SitemapGenerate;

class SitemapController extends Controller
{
    public function createSitemap($website_slug){

        return response()->json(SitemapGenerate::CreateFile($website_slug));
    }
  
}
