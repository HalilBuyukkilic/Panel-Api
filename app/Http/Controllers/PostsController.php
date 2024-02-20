<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Validator;
use Carbon\Carbon;
use App\Models\perasisHizmetler;
use App\Models\perasisImage;
use App\Models\Tag;
use App\Models\Role;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Post;
use App\Utils\SitemapGenerate;
use App\Utils\MediaManager;

class PostsController extends Controller
{

    /**
     * List post - for unauthorized clients
     */
    public function index(Request $request, $website_slug, $post_type_id)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        // $data=DB::table('posts')
        //     ->where('posts.post_type_id', '=', $post_type_id)
        //     ->where('posts.website_id', '=', $website->id)
        //     ->leftjoin('posts_tags','posts_tags.post_id','=','posts.id')
        //     ->leftjoin('tags','tags.id','=','posts_tags.tag_id')
        //     ->leftjoin('users as u1','u1.id','=','posts.created_by')
        //     ->leftjoin('users as u2','u2.id','=','posts.approved_by')
        //     ->leftjoin('durum', 'durum.id','=','posts.status_id') 
        //     ->leftjoin('dil','dil.id','=','posts.language_id')
        //     ->leftjoin('image', function($join) {
        //         $join->on('image.image_ID','=','posts.id');
        //         //$join->where('image.sayfa_ID', '=', 3);
        //     })
        //     ->select( 'posts.id','posts.title','posts.summary', 'posts.content','posts.slug','posts.created_at',
        //     'posts.updated_at','u1.username as ekleyen','u2.username as onaylayan','durum.aciklama as durum','dil.dil',
        //     'image.imageUrl','image.tag_title','image.tag_alt','tags.tag_name'
        //     )

        //     ->orderBy('id', 'DESC')
        //     ->get();
        $query = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
            ->where('post_type_id', '=', $post_type_id)
            ->where('website_id', '=', $website->id)
            ->where('status_id', '=', 2)
            ->where(function ($query) use ($searchText) {
                if (!isset($searchText))
                    return;
                $query->where('title', 'like', '%' . $searchText . '%');
                $query->orWhere('summary', 'like', '%' . $searchText . '%');
                $query->orWhere('content', 'like', '%' . $searchText . '%');
                $query->orWhere('meta_title', 'like', '%' . $searchText . '%');
                $query->orWhere('meta_desc', 'like', '%' . $searchText . '%');
            })
            ->orderBy('id', 'DESC');

        //return  response()->json($query->paginate($perPage, ['*'], 'page', $page));

        $veri = $query->get();

        return  response()->json($veri);
    }

    /**
     * List posts - for authorized clients
     */
    public function list(Request $request, $website_slug, $post_type_id)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }


        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();



        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_list',$stack)){


            $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
                ->where('post_type_id', '=', $post_type_id)
                ->where('website_id', '=', $website->id)
                ->where(function ($query) use ($searchText) {
                    if (!isset($searchText))
                        return;
                    $query->where('title', 'like', '%' . $searchText . '%');
                    $query->orWhere('summary', 'like', '%' . $searchText . '%');
                    $query->orWhere('content', 'like', '%' . $searchText . '%');
                    $query->orWhere('meta_title', 'like', '%' . $searchText . '%');
                    $query->orWhere('meta_desc', 'like', '%' . $searchText . '%');
                })
                ->orderBy('id', 'DESC')
                ->get();

            return  response()->json($data);
        } else {
            return response()->json('unauthorized');
        }
    }



    /**
     * Insert post - for authorized clients
     */
    public function insert(Request $request, $website_slug, $post_type_id)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }


        $searchText = $request->input('search');
        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_post',$stack)){
            try {
                $request->merge([
                    'slug' => Str::slug($request->title)
                ]);

                $approved_by = null;

                if ($request->input('status_id') == '2') {
                    $approved_by = Auth::id();
                }
                //insert post

                $publish_date = '';

                if ($request->input('publish_date')) {
                    $publish_date = $request->input('publish_date');
                } else {
                    $publish_date = Carbon::now();
                }

                if ($request->input('selectAuthor')) {
                    $selectAuthor = $request->input('selectAuthor');
                } else {
                    $selectAuthor = Auth::id();
                }


                $post = Post::create([
                    'title' => $request->input('title'),
                    'slug' => $request->input('slug'),
                    'summary' => $request->input('summary'),
                    'content' => $request->input('content'),
                    'category_id' => $request->input('category_id'),
                    'meta_title'  => $request->input('meta_title'),
                    'meta_desc' => $request->input('meta_desc'),
                    'keywords' => $request->input('keywords'),
                    //'tags'  =>$request->input('tags'),
                    'status_id' => $request->input('status_id'),
                    'language_id' => 1, //$request->input('language_id'),
                    'post_type_id' => $post_type_id,
                    'website_id' => $website->id,
                    'created_by' => $selectAuthor,
                    'approved_by' => $approved_by,
                    'published_at' => $publish_date
                ]);

                //insert post image
                if ($post != null && $post->id > 0) {

                    $tags = $request->input('tags'); // 'kalem,elma,armut'
                    if ($tags != null) {
                        foreach (explode(',', $tags) as $tag) {

                            $tag_db = null;

                            $tag_db = Tag::where('tag_name', '=', $tag)
                                ->where('website_id', '=', $website->id)
                                ->first();


                            if ($tag_db == null) {
                                $tag_db = new Tag();
                                $tag_db->tag_name = $tag;
                                $tag_db->slug = Str::slug($tag);
                                $tag_db->website_id = $website->id;
                                $tag_db->save();
                            }

                            DB::table('posts_tags')->insert(['post_id' => $post->id, 'tag_id' => $tag_db->id]);
                        }
                    }


                    // request()->validate([
                    //     'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    // ]);



                    if ($file = $request->hasFile('image')) {

                        $params = array();
                        $params['media_type'] = MediaManager::IMAGE;
                        $params['post_id'] = $post->id;
                        $params['website_id'] = $website->id;
                        $params['website_slug'] = $website->slug;
                        $params['file'] = $request->file('image');
                        $params['file_name'] = $post->slug;
                        $params['file_extension'] = request()->image->getClientOriginalExtension();
                        $params['title'] = $post->title;
                        $params['tag_title'] = $post->title;
                        $params['tag_alt'] = $post->title;
                        $params['dimensions'] = $request->input('dimensions');
                        $params['fileformat'] = $request->input('fileformat');
                        $params['filesize'] = $request->input('filesize');
                        $mediaResult = MediaManager::CreateMedia($params);

                        if ($mediaResult['Success']) {
                            $post->media_id = $mediaResult['Media']['id'];
                            $post->save();
                        }
                    }

                    SitemapGenerate::CreateFile($website_slug);
                }

                $result = array('Success' => true, 'Message' => $post_type->title . ' eklendi', 'slug' => $post->slug);

                return response()->json($result);
            } catch (QueryException $e) {
                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    $result = array('DublicateError' => 'duplicate');
                    return $result;
                }

                $result = array('Error:' => json_encode($e));
                return $result;
            }
        }

        return response()->json('unauthorized');
    }

    public function randomIndex(Request $request, $website_slug, $post_type_id)
    {



        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $data = DB::table('posts')
            ->where('posts.post_type_id', '=', $post_type_id)
            ->where('posts.website_id', '=', $website->id)
            ->leftjoin('users as u1', 'u1.id', '=', 'posts.created_by')
            ->leftjoin('users as u2', 'u2.id', '=', 'posts.approved_by')
            ->leftjoin('durum', 'durum.id', '=', 'posts.status_id')
            ->leftjoin('dil', 'dil.id', '=', 'posts.language_id')
            ->leftjoin('image', function ($join) {
                $join->on('image.image_ID', '=', 'posts.id');
                //$join->where('image.sayfa_ID', '=', 5);
            })
            ->select(
                'posts.id',
                'posts.title',
                'posts.summary',
                'posts.content',
                'posts.slug',
                'posts.created_at',
                'posts.updated_at',
                'u1.username as ekleyen',
                'u2.username as onaylayan',
                'durum.aciklama as durum',
                'dil.dil',
                'image.imageUrl',
                'image.tag_title',
                'image.tag_alt'
            )
            ->where('durum.aciklama', '=', 'Yayında')
            ->get();

        if (count($data) > 4) {
            return  response()->json($data->random(4));
        } else {
            return  response()->json($data);
        }
    }

    public function update(Request $request, $website_slug, $slug, $post_type_id)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }



        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_put',$stack)){

            try {
                $post = Post::where('slug', $slug)->first();
                $ifChangeName = $post->slug;
                // $old_image=perasisImage::where('image_ID',$post->id)->first();

                if ($post != null) {
                    $request->merge([
                        'slug' => Str::slug($request->title)
                    ]);

                    $approved_by = null;
                    $published_at = null;
                    if ($request->input('status_id') == '2') {
                        $approved_by = Auth::id();
                        //$published_at=now();
                    }
                    if ($request->input('created_by')) {
                        $selectAuthor = $request->input('created_by');
                    } else {
                        $selectAuthor = Auth::id();
                    }


                    $update_params = [
                        'title' => $request->input('title'),
                        'slug' => $request->input('slug'),
                        'summary' => $request->input('summary'),
                        'content' => $request->input('content'),
                        'category_id' => $request->input('category_id'),
                        'meta_title'  => $request->input('meta_title'),
                        'meta_desc' => $request->input('meta_desc'),
                        'keywords' => $request->input('keywords'),
                        //'tags'  =>$request->input('tags'),
                        'status_id' => $request->input('status_id'),
                        'language_id' => $request->input('language_id'),
                        'post_type_id' => $post_type_id,
                        'website_id' => $website->id,
                        'updated_by' => Auth::id(),
                        'created_by' => $selectAuthor,
                        'approved_by' => $approved_by
                    ];

                    if ($request->input('published_at')) {
                        $update_params['published_at'] = $request->input('published_at');
                    }

                    $post->update($update_params);

                    $tags = $request->input('tags');
                    DB::table('posts_tags')->where('post_id', '=', $post->id)->delete();
                    if ($tags != null) {
                        $tags_array = [];


                        foreach (explode(',', $tags) as $tag) {

                            $tag_db = null;

                            $tag_db = Tag::where('tag_name', '=', $tag)
                                ->where('website_id', '=', $website->id)
                                ->first();

                            if ($tag_db == null) {
                                $tag_db = new Tag();
                                $tag_db->tag_name = $tag;
                                $tag_db->slug = Str::slug($tag);
                                $tag_db->website_id = $website->id;
                                $tag_db->save();
                            }

                            DB::table('posts_tags')->insert(['post_id' => $post->id, 'tag_id' => $tag_db->id]);

                            //array_push($tags_array,$tag_db);

                        }

                        // $post->tags()->sync($tags_array);

                    }


                    if ($file = $request->hasFile('image')) {

                        $params = array();
                        $params['media_type'] = MediaManager::IMAGE;
                        $params['post_id'] = $post->id;
                        $params['media_id'] = $post->media_id;
                        $params['website_id'] = $website->id;
                        $params['website_slug'] = $website->slug;
                        $params['file'] = $request->file('image');
                        $params['file_name'] = $post->slug;
                        $params['file_extension'] = request()->image->getClientOriginalExtension();
                        $params['title'] = $post->title;
                        $params['tag_title'] = $post->title;
                        $params['tag_alt'] = $post->title;
                        $params['dimensions'] = $request->input('dimensions');
                        $params['fileformat'] = $request->input('fileformat');
                        $params['filesize'] = $request->input('filesize');
                        $params['ifchange'] = $ifChangeName;
                        $params['orginal'] = $post->slug;
                        $mediaResult = MediaManager::UpdateMedia($params);
                        return $mediaResult;
                    }

                    SitemapGenerate::CreateFile($website_slug);

                    $result = array('Success' => true, 'Message' => $post_type->title . ' güncellendi');

                    return response()->json($result);
                } else {

                    $result = array('Success' => false, 'Message' => 'Güncelleme olurken bir hata oluştu.Güncellemek istediğiniz ' . $post_type->title . ' silinmiş olabilir');

                    return response()->json($result);
                }
            } catch (QueryException $e) {
                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    $result = array('DublicateError' => 'duplicate');
                    return $result;
                }

                $result = array('Error:' => json_encode($e));
                return $result;
            }
        } else {
            return response()->json('unauthorized');
        }
    }

    public function delete(Request $request, $website_slug, $slug, $post_type_id)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }

        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_delete',$stack)){
            $post = Post::where('slug', $slug)->first();
            $post['media_type'] = MediaManager::IMAGE;

            $mediaResult = MediaManager::DeleteMedia($post);
            $post->delete();

            return response()->json($mediaResult);


            // $image=perasisImage::where('image_ID',$post->id)->first();

            // if($post==null){
            //         $result = array('Success' => true , 'Message' => 'Bu içerik silinmiş veya hiç eklenmemiş olabilir');     
            //         return response()->json($result);
            // }

            // if($image!=null){
            //     DB::table('image')
            //         ->where('imageName',$slug)
            //         //->where('image.sayfa_ID', '=', 3)
            //         ->delete(); 
            //     Storage::delete('public/images/'. $website->slug . '/'. $post_type->slug .'/'.$image->imageUrl);
            // }

            // $post->delete();


            SitemapGenerate::CreateFile($website_slug);

            $result = array('Success' => true, 'Message' => 'İçerik silindi');

            return response()->json($result);
        } else {
            return response()->json('unauthorized');
        }
    }

    public function publicShow($website_slug, $slug, $post_type_id)
    {

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
            ->where('post_type_id', '=', $post_type_id)
            ->where('website_id', '=', $website->id)
            ->where('posts.status_id', '=', 2)
            ->where('slug', '=', $slug)
            ->get();
        if ($data != null) {
            return response()->json($data);
        } else {
            $result = array('Success' => false, 'Message' => 'Böyle bir içerik mevcut değildir.');
            return response()->json($result);
        }
    }



    public function show(Request $request, $website_slug, $slug, $post_type_id)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }

        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array('hizmetler_show',$stack)){
            $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
                ->where('post_type_id', '=', $post_type_id)
                ->where('website_id', '=', $website->id)
                ->where('slug', '=', $slug)
                ->get();
            if ($data != null) {
                return response()->json($data);
            } else {
                $result = array('Success' => false, 'Message' => 'Böyle bir haber bulunmamaktadır,silinmiş veya hiç eklenmemiş olabilir');
                return response()->json($result);
            }
        } else {
            return response()->json('unauthorized');
        }
    }

    //verilen post typea göre kategori ve onun altında post'ları getirir
    // destek sayfası
    public function categoriesWithPosts(Request $request, $website_slug, $post_slug, $post_type_id)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }


        $query = Category::with(['posts' => function ($join) use ($post_type) {
            $join->where('posts.status_id', '=', 2)
                ->where('posts.post_type_id', '=', $post_type->id);
        }])
            ->where('kategori.post_type_id', '=', $post_type_id)
            ->where('kategori.website_id', '=', $website->id)
            ->where(function ($query) use ($searchText) {
                if (!isset($searchText))
                    return;
                $query->where('kategori.kategori', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.title', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.summary', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.content', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.meta_title', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.meta_desc', 'like', '%' . $searchText . '%');
            })
            ->orderBy('kategori.id', 'ASC');

        $veri = $query->get();

        return  response()->json($veri);
    }

    //belirli bir kategorinin altındaki postları getirir
    //belirli bir kategorinin post arşiv sayfası
    public function categoryPosts(Request $request, $website_slug, $post_slug, $category_slug)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_slug)
            ->first();

        $category = DB::table('kategori')
            ->where('kategori.slug', '=', $category_slug)
            ->where('website_id', '=', $website->id)
            ->where('post_type_id', '=', $post_type->id)
            ->first();

        if ($post_type == null || $website == null || $category == null) {
            return response()->json('bad request');
        }

        $query = DB::table('posts')
            //->where('post_type_id', '=', $post_type_id)
            ->where('website_id', '=', $website->id)
            ->where('category_id', '=', $category->id)
            //->where('posts.status_id', '=', 2)
            ->where(function ($query) use ($searchText) {
                if (!isset($searchText))
                    return;
                $query->where('kategori.kategori', 'like', '%' . $searchText . '%');
                $query->where('posts.title', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.summary', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.content', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.meta_title', 'like', '%' . $searchText . '%');
                $query->orWhere('posts.meta_desc', 'like', '%' . $searchText . '%');
            })
            ->orderBy('id', 'ASC');

        $veri = $query->get();

        return  response()->json($veri);
    }

    public function categoryList(Request $request, $website_slug, $post_slug, $category_slug)
    {
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $category = DB::table('kategori')
            ->where('kategori.slug', '=', $category_slug)
            ->where('post_type_id', '=', $post_type->id)
            ->where('website_id', '=', $website->id)
            ->first();

        if ($post_type == null || $website == null || $category == null) {
            return response()->json('bad request');
        }

        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
            ->where('post_type_id', '=', $post_type->id)
            ->where('website_id', '=', $website->id)
            ->where('category_id', '=', $category->id)
            ->where('posts.status_id', '=', 2)
            ->get();

        if ($data != null) {
            return response()->json($data);
        } else {
            $result = array('Success' => false, 'Message' => 'B�yle bir haber bulunmamaktad�r,silinmi� veya hi� eklenmemi� olabilir');
            return response()->json($result);
        }
    }



    public function tag(Request $request, $website_slug, $tag_name, $post_type_id)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $tag = DB::table('tags')
            ->where('slug', '=', $tag_name)
            ->where('website_id', '=',  $website->id)
            ->get();


        $post_ids = DB::table('posts_tags')
            ->where('tag_id', '=', $tag[0]->id)
            ->select('post_id')
            ->get();

        $order = array();
        // return count($post_ids);
        foreach ($post_ids as $key) {
            array_push($order, $key->post_id);
        }



        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
            ->where('post_type_id', '=', $post_type_id)
            ->where('website_id', '=', $website->id)
            ->where('status_id', '=', 2)
            ->whereIn('id', $order)
            ->where(function ($query) use ($searchText) {
                if (!isset($searchText))
                    return;
                $query->where('title', 'like', '%' . $searchText . '%');
                $query->orWhere('summary', 'like', '%' . $searchText . '%');
                $query->orWhere('content', 'like', '%' . $searchText . '%');
                $query->orWhere('meta_title', 'like', '%' . $searchText . '%');
                $query->orWhere('meta_desc', 'like', '%' . $searchText . '%');
            })
            ->get();

        if ($data != null) {
            return response()->json($data);
        } else {
            $result = array('Success' => false, 'Message' => 'B�yle bir haber bulunmamaktad�r,silinmi� veya hi� eklenmemi� olabilir');
            return response()->json($result);
        }
    }


    public function allTags($website_slug, $post_type_id)
    {


        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $tags = DB::table('tags')
            ->where('tags.website_id', '=', $website->id)
            ->join('posts_tags', 'posts_tags.tag_id', '=', 'tags.id')
            ->join('posts', 'posts.id', '=', 'posts_tags.post_id')
            ->where('posts.post_type_id', '=', $post_type->id)
            ->select('tags.tag_name', 'tags.slug','posts.title')
            ->distinct('tags.tag_name')
            ->get();

        $result_tags = array();
        foreach ($tags as $value) {
            $stack = array('tag' => $value->tag_name, 'slug' => $value->slug);
            array_push($result_tags, $stack);
        }

        return response()->json($tags);
    }

    public function related($website_slug, $slug, $post_type_id)
    {
       

        $post_type = DB::table('post_types')
            ->where('post_types.id', '=', $post_type_id)
            ->first();
        
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $post = Post::with(['tags', 'dil'])
            ->where('post_type_id', '=', $post_type_id)
            ->where('website_id', '=', $website->id)
            ->where('slug', '=', $slug)
            ->first();
            
       
        if($post == null || $post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $related_tags = array();
        $post_ids = array();
        $related_posts = array();

        if ($post->tags != null && count($post->tags) > 0) {
            foreach ($post->tags as $tag) {
                array_push($related_tags, $tag['id']);
            }

            $post_ids = DB::table('posts_tags')
                ->whereIn('tag_id',  $related_tags)
                ->where('post_id', '!=', $post->id)
                ->select('post_id')
                ->distinct('post_id')
                ->get();

            $related_post_ids = array();
            foreach ($post_ids as $value) {
                array_push($related_post_ids, $value->post_id);
            }

            $related_posts = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
                ->where('post_type_id', '=', $post_type_id)
                ->where('website_id', '=', $website->id)
                ->whereIn('id', $related_post_ids)
                ->where('status_id', '=', 2)
                ->take(10)
                ->get();
        }
        if ($related_posts == null || count($related_posts) == 0) {
            $related_posts = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
                ->where('post_type_id', '=', $post_type_id)
                ->where('website_id', '=', $website->id)
                ->where('id', '!=', $post->id)
                ->where('status_id', '=', 2)
                ->take(10)
                ->get();
        }

        return response()->json(count($related_posts) > 4 ? $related_posts->random(4) : $related_posts);
    }


    public function trySitemap($website_slug)
    {
        return SitemapGenerate::CreateFile($website_slug);
    }
}
