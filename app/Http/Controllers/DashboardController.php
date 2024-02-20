<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder; 

class DashboardController extends Controller{


    public function SitesCount(Request $request){
        $count_all_sites = DB::table('websites')->count();
        $count_active_sites = DB::table('websites')->where('status_id', 2)->count();
        $count_passive_sites = DB::table('websites')->where('status_id', 1)->count();

        $result = array('Success' => true , 'All' => $count_all_sites, 'Active' => $count_active_sites, 'Passive' => $count_passive_sites);
        return response()->json($result);
    }

    public function ContentsCount(Request $request){
        $count_all_posts = DB::table('posts')->count();
        $count_active_posts = DB::table('posts')->where('status_id', 2)->count();
        $count_passive_posts = DB::table('posts')->where('status_id', 1)->count();

        $result = array('Success' => true , 'All' => $count_all_posts, 'Active' => $count_active_posts, 'Passive' => $count_passive_posts);
        return response()->json($result);
    }

    public function MediasCount(Request $request){
        $count_all_medias = DB::table('media')->count();
        $count_file_medias = DB::table('media')->where('media_type_id', 2)->count();
        $count_image_medias = DB::table('media')->where('media_type_id', 1)->count();

        $result = array('Success' => true , 'All' => $count_all_medias, 'File' => $count_file_medias, 'Image' => $count_image_medias);
        return response()->json($result);
    }

    public function UsersCount(Request $request){
        $count_all_users = DB::table('users')->count();
        $count_active_users = DB::table('users')->where('durum_ID', 2)->count();
        $count_passive_users = DB::table('users')->where('durum_ID', 1)->count();

        $result = array('Success' => true , 'All' => $count_all_users, 'Active' => $count_active_users, 'Passive' => $count_passive_users);
        return response()->json($result);
    }

}