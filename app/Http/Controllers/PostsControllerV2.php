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
use App\Models\PostType;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Language;
use App\Models\Post;
use App\Models\Media;
use App\Utils\SitemapGenerate;
use App\Utils\MediaManager;
use App\Models\Website;
use App\Models\WebsiteOption;
use App\Models\RedirectUrl;

class PostsControllerV2 extends Controller
{

    /**
     * List post - for unauthorized clients
     */
    public function index(Request $request, $website_slug, $post_type_slug)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $query = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
            ->where('post_type_id', '=', $post_type->id)
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
    public function list(Request $request, $website_slug, $post_type_slug)
    {

        // $u = Auth::user(); 
        // $u->load('roles.permissions'); 
        // $roleLimit=count($u->roles);  
        // $stack = array();
        // $permission=Permission::all();      
        // //  return $u->roles[1]->permissions;

        // for($x = 0; $x < $roleLimit; $x++)
        // {  
        //     $permissionLimit=count($u->roles[$x]->permissions);

        //     for($y = 0; $y < $permissionLimit; $y++)
        //     {
        //         array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
        //     }

        // }


        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();



        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_list', $stack)){


            $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media','websites'])
                ->where('post_type_id', '=', $post_type->id)
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

    public function allpostslist(Request $request)
    {
        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori'])
            ->where('status_id',2)
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }

    

    public function sitepostslist($website_slug)
    {
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();


        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori'])
            ->where('website_id', '=', $website->id)
            ->where('status_id',2)
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }

    public function websitepostslist(Request $request, $website_slug, $post_type_slug, $slug)
    {
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $data = RedirectUrl::with(['posts', 'post_type', 'websites'])
            ->where('website_id', '=', $website->id)
            ->where('post_type_id', '=', $post_type->id)
            ->where('slug', '=', $slug)
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }
    /**
     * Insert post - for authorized clients
     */
    public function insert(Request $request, $website_slug, $post_type_slug)
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
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_post', $stack)){
            try {
                $request->merge([
                    'slug' => Str::slug($request->title)
                ]);

                $approved_by = null;

                if ($request->input('status_id') == '2') {
                    $approved_by = Auth::id();
                }

                $meta_title = $request->input('meta_title');

                if ($meta_title == null || trim($meta_title) == '')
                    $meta_title = $request->input('title');

                $publish_date = '';

                if ($request->input('publish_date')) {
                    $publish_date = $request->input('publish_date');
                } else {
                    $publish_date = Carbon::now();
                }

                if ($request->input('created_by')) {
                    $selectAuthor = $request->input('created_by');
                } else {
                    $selectAuthor = Auth::id();
                }

                if ($request->input('language_id')) {
                    $selectLanguage = $request->input('language_id');
                } else {
                    $selectLanguage = 1;
                }



                //insert post
                $post = Post::create([
                    'title' => $request->input('title'),
                    'slug' => $request->input('slug'),
                    'summary' => $request->input('summary'),
                    'content' => $request->input('content'),
                    'category_id' => $request->input('category_id'),
                    'meta_title'  => $meta_title,
                    'meta_desc' => $request->input('meta_desc'),
                    'keywords' => $request->input('keywords'),
                    //'tags'  =>$request->input('tags'),
                    'status_id' => $request->input('status_id'),
                    'language_id' => $selectLanguage,
                    'post_type_id' => $post_type->id,
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



                    // SitemapGenerate::CreateFile($website_slug);
                }

                $result = array('Success' => true, 'Message' => $post_type->title . ' eklendi', 'id' => $post->id, 'postType' => $post_type->title_plural);

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


    public function randomIndex(Request $request, $website_slug, $post_type_slug)
    {



        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $data = DB::table('posts')
            ->where('posts.post_type_id', '=', $post_type->id)
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

    public function update(Request $request, $website_slug, $post_type_slug, $post_id)
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
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_put', $stack)){

            try {
                $post = Post::where('id', $post_id)->first();
                $ifChangeName = $post->slug;
                // $old_image=perasisImage::where('image_ID', $post->id)->first();

                if ($post != null) {
                    $request->merge([
                        'slug' => Str::slug($request->slug)
                    ]);

                    $approved_by = null;
                    $published_at = null;
                    if ($request->input('status_id') == '2') {
                        $approved_by = Auth::id();
                        $published_at = now();
                    }

                    $meta_title = $request->input('meta_title');

                    if ($meta_title == null || trim($meta_title) == '')
                        $meta_title = $request->input('title');

                    if ($request->input('created_by')) {
                        $selectAuthor = $request->input('created_by');
                    } else {
                        $selectAuthor = Auth::id();
                    }

                    $post->update([
                        'title' => $request->input('title'),
                        'slug' => $request->input('slug'),
                        'summary' => $request->input('summary'),
                        'content' => $request->input('content'),
                        'category_id' => $request->input('category_id'),
                        'meta_title'  => $meta_title,
                        'meta_desc' => $request->input('meta_desc'),
                        'keywords' => $request->input('keywords'),
                        //'tags'  =>$request->input('tags'),
                        'status_id' => $request->input('status_id'),
                        'language_id' => $request->input('language_id'),
                        'post_type_id' => $post_type->id,
                        'website_id' => $website->id,
                        'created_by' => $selectAuthor,
                        'published_at' => $published_at,
                        'approved_by' => $approved_by,

                    ]);


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

                            //array_push($tags_array, $tag_db);

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

                    //  SitemapGenerate::CreateFile($website_slug);

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

    public function delete(Request $request, $website_slug, $post_type_slug, $post_id)
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
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array($post_type->slug . '_delete', $stack)){
            $post = Post::where('id', $post_id)->first();
            $post['media_type'] = MediaManager::IMAGE;

            $mediaResult = MediaManager::DeleteMedia($post);
            $post->delete();

            return response()->json($mediaResult);


            // $image=perasisImage::where('image_ID', $post->id)->first();

            // if($post==null){
            //         $result = array('Success' => true , 'Message' => 'Bu içerik silinmiş veya hiç eklenmemiş olabilir');     
            //         return response()->json($result);
            // }

            // if($image!=null){
            //     DB::table('image')
            //         ->where('imageName', $slug)
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

    public function publicShow($website_slug, $post_type_slug, $slug)
    {

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
            ->where('post_type_id', '=', $post_type->id)
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

    public function postTypeList()
    {

        $data = DB::table('post_types')
            ->leftjoin('websites', 'websites.id', '=', 'post_types.website_id')
            ->select(
                'post_types.id',
                'post_types.title',
                'post_types.title_plural',
                'websites.title as website',
                'post_types.seo_enabled',
                'post_types.media_enabled',
                'post_types.summary_enabled',
                'post_types.url_enabled',
                'post_types.author_enabled',
                'post_types.tag_enabled',
                'post_types.category_enabled',
                'post_types.language_enabled',
                'post_types.alldelete_enabled',
                'post_types.url_type'
            )
            ->get();

        return  response()->json($data);
    }

    public function postTypeUpdate(Request $request, $id)
    {

        $post_type = PostType::findOrfail($id);

        if ($post_type != null) {


            $post_type->update($request->all());
            $result = array('Success' => true, 'Message' => 'post type güncellendi');

            return response()->json($result);
        } else {

            $result = array('Success' => false, 'Message' => 'Güncelleme olurken bir hata oluştu.Güncellemek istediğiniz post type silinmiş olabilir');

            return response()->json($result);
        }
    }


    public function show(Request $request, $website_slug, $post_type_slug, $post_id)
    {

        // $u = Auth::user(); 
        // $u->load('roles.permissions'); 
        // $roleLimit=count($u->roles);  
        // $stack = array();
        // $permission=Permission::all();      
        // //  return $u->roles[1]->permissions;

        // for($x = 0; $x < $roleLimit; $x++)
        // {  
        //     $permissionLimit=count($u->roles[$x]->permissions);

        //     for($y = 0; $y < $permissionLimit; $y++)
        //     {
        //         array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
        //     }

        // }

        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        if (true) { //in_array('hizmetler_show', $stack)){
            $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media', 'websites'])
                ->where('post_type_id', '=', $post_type->id)
                ->where('website_id', '=', $website->id)
                ->where('id', '=', $post_id)
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

    public function categoriesWithPosts(Request $request, $website_slug, $post_slug, $post_type_slug)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
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
            ->where('kategori.post_type_id', '=', $post_type->id)
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

        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $category = DB::table('kategori')
            ->where('kategori.slug', '=', $category_slug)
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


    public function tagShow(Request $request, $website_slug, $tag_name)
    {
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $tag = DB::table('tags')
            ->where('slug', '=', $tag_name)
            ->where('website_id', '=', $website->id)
            ->get();
        return response()->json($tag);
    }


    public function tag(Request $request, $website_slug, $tag_name, $post_type_slug)
    {

        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($post_type == null || $website == null) {
            return response()->json('bad request');
        }

        $tag = DB::table('tags')
            ->where('tag_name', '=', $tag_name)
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
            ->where('post_type_id', '=', $post_type->id)
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

    // public function tagPostList(Request $request, $website_slug, $post_type_slug, $tag_name)
    // {

    //     $searchText = $request->input('search');
    //     $page = $request->input('page');
    //     $perPage = $request->input('perPage');
    //     $post_type = DB::table('post_types')
    //         ->where('post_types.slug', '=', $post_type_slug)
    //         ->first();

    //     $website = DB::table('websites')
    //         ->where('websites.slug', '=', $website_slug)
    //         ->first();
    
    //     if ($post_type == null || $website == null) {
    //         return response()->json('bad request');
    //     }

    //     $tag = DB::table('tags')
    //         ->where('tag_name', '=', $tag_name)
    //         ->get();


    //     $post_ids = DB::table('posts_tags')
    //         ->where('tag_id', '=', $tag[0]->id)
    //         ->select('post_id')
    //         ->get();

    //     $order = array();
    //     // return count($post_ids);
    //     foreach ($post_ids as $key) {
    //         array_push($order, $key->post_id);
    //     }



    //     $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
    //         ->where('post_type_id', '=', $post_type->id)
    //         ->where('website_id', '=', $website->id)
    //         ->where('status_id', '=', 2)
    //         ->whereIn('id', $order)
    //         ->where(function ($query) use ($searchText) {
    //             if (!isset($searchText))
    //                 return;
    //             $query->where('title', 'like', '%' . $searchText . '%');
    //             $query->orWhere('summary', 'like', '%' . $searchText . '%');
    //             $query->orWhere('content', 'like', '%' . $searchText . '%');
    //             $query->orWhere('meta_title', 'like', '%' . $searchText . '%');
    //             $query->orWhere('meta_desc', 'like', '%' . $searchText . '%');
    //         })
    //         ->get();

    //     if ($data != null) {
    //         return response()->json($data);
    //     } else {
    //         $result = array('Success' => false, 'Message' => 'B�yle bir posts bulunmamaktad�r,silinmi� veya hi� eklenmemi� olabilir');
    //         return response()->json($result);
    //     }
    // }


    public function tagPostList(Request $request, $website_slug, $post_typename ,$tag_name)
    {
       
        $searchText = $request->input('search');
        $page = $request->input('page');
        $perPage = $request->input('perPage');
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_typename)
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
            ->where('post_type_id', '=', $post_type->id)
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



    public function allTagPostList(Request $request, $website_slug,$tag_name)
    {
       
     

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();
      
        if ($website == null) {
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
            ->where('website_id', '=', $website->id)
            ->where('status_id', '=', 2)
            ->whereIn('id', $order)
            ->get();

        if ($data != null) {
            return response()->json($data);
        } else {
            $result = array('Success' => false, 'Message' => 'B�yle bir POST bulunmamaktad�r,silinmi� veya hi� eklenmemi� olabilir');
            return response()->json($result);
        }
    }

    
    
    public function allPostTypeTags($website_slug, $post_typename)
    {


        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_typename)
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
            
        return $tags;

        $result_tags = array();
        foreach ($tags as $value) {
            $stack = array('tag' => $value->tag_name, 'slug' => $value->slug);
            array_push($result_tags, $stack);
        }

        return response()->json($tags);
    }



    public function allTags($website_slug, $post_type_slug)
    {


        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
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
            ->select('tags.tag_name')
            ->distinct('tags.tag_name')
            ->get();

        $result_tags = array();
        foreach ($tags as $value) {
            array_push($result_tags, $value->tag_name);
        }

        return response()->json($result_tags);
    }
    

    
    public function alltagListName($website_slug)
    {


        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if ($website == null) {
            return response()->json('bad request');
        }

        $tags = DB::table('tags')
            ->where('tags.website_id', '=', $website->id)
            ->join('posts_tags', 'posts_tags.tag_id', '=', 'tags.id')
            ->join('posts', 'posts.id', '=', 'posts_tags.post_id')
            ->select('tags.tag_name','tags.slug')
            ->distinct('tags.tag_name')
            ->get();


        return response()->json($tags);
    }


    public function related($website_slug, $slug, $post_type_slug)
    {


        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $post = Post::with(['tags', 'dil'])
            ->where('post_type_id', '=', $post_type->id)
            ->where('website_id', '=', $website->id)
            ->where('slug', '=', $slug)
            ->first();

        if ($post == null || $post_type == null || $website == null) {
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
                ->where('post_type_id', '=', $post_type->id)
                ->where('website_id', '=', $website->id)
                ->whereIn('id', $related_post_ids)
                ->where('status_id', '=', 2)
                ->take(10)
                ->get();
        }
        if ($related_posts == null || count($related_posts) == 0) {
            $related_posts = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media'])
                ->where('post_type_id', '=', $post_type->id)
                ->where('website_id', '=', $website->id)
                ->where('id', '!=', $post->id)
                ->where('status_id', '=', 2)
                ->take(10)
                ->get();
        }

        return response()->json(count($related_posts) > 4 ? $related_posts->random(4) : $related_posts);
    }

    public function languageList($website_slug)
    {
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();
        $data = DB::table('dil')
            ->select('dil.id', 'dil.dil', 'dil.aciklama')
            ->where('website_id', '=', $website->id)
            ->get();

        return  response()->json($data);
    }

    public function mediaImageAllList()
    {
        $data = DB::table('media')
            ->where('media_type_id', '=', 1)
            ->get();

        return  response()->json($data);
    }

    public function mediaImagePanelshow($id)
    {
        $data = Media::where('id', $id)->first();

        return  response()->json($data);
    }

    public function mediaImagePanelupdate(Request $request,$id)
    {
    
        $media = Media::where('id', $id)->first();
        $mediafilename=$media->file_name;
        $filetitle=$request->input('file_name');
        $urlexplode = explode("/", $media['url']);
        $imageExt =explode(".",$mediafilename);
     
        if (($filetitle != $mediafilename) && ($filetitle != null)) {
            $mediaurl =$urlexplode[0]."/".$urlexplode[1]."/".$urlexplode[2]."/".$urlexplode[3]."/". $urlexplode[4]."/". $urlexplode[5]."/".$filetitle.".".$imageExt[1];           
                $rawContent=Storage::get($media['file_path'].$mediafilename);
                Storage::put($media['file_path'].$filetitle.".".$imageExt[1],$rawContent  );
                Storage::delete($media['file_path'].$mediafilename);
               
                $media->update([
                    'file_name' => $filetitle.".".$imageExt[1],
                    'tag_title'  => $request->input('tag_title'),
                    'tag_alt' => $request->input('tag_alt'),
                    'dimensions' => $request->input('dimensions'),
                    'url' => $mediaurl,
                ]);
        }else{
           
            $media->update([
                'tag_title'  => $request->input('tag_title'),
                'tag_alt' => $request->input('tag_alt'),
                'dimensions' => $request->input('dimensions'),
            ]);
        }
        

        $result = array('Success' => true);

        return response()->json($result);
    }


    public function mediaImageList($website_slug)
    {
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $data = DB::table('media')
            ->where('media.website_id', '=', $website->id)
            ->where('media_type_id', '=', 1)
            ->get();

        return  response()->json($data);
    }

    public function mediaImageInsert(Request $request, $website_slug)
    {


        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $success = false;
        $image_url = '';
        $media_id = -1;

        if ($file = $request->hasFile('image')) {

            $params = array();
            $params['media_type'] = MediaManager::IMAGE;
            $params['website_id'] = $website->id;
            $params['website_slug'] = $website->slug;
            $params['file'] = $request->file('image');
            $params['file_name'] = Str::slug($request->input('title'));
            $params['file_extension'] = request()->image->getClientOriginalExtension();
            $params['title'] =  $request->input('title');
            $params['post_id'] = null;
            $params['tag_title'] = $request->input('tag_title');
            $params['tag_alt'] =  $request->input('tag_alt');
            $params['dimensions'] = $request->input('dimensions');
            $params['fileformat'] = $request->input('fileformat');
            $params['filesize'] = $request->input('filesize');

            $mediaResult = MediaManager::CreateMedia($params);

            $success = $mediaResult['Success'];

            if ($success) {
                $media_id = $mediaResult['Media']->id;
                $image_url = $mediaResult['Media']->url;
            }
        }

        return json_encode(array('Success' => $success, 'MediaID' => $media_id, 'ImageUrl' => $image_url));
    }

    public function mediaImageDelete(Request $request, $id)
    {

        $media = Media::where('id', $id)->first();

        if ($media != null) {
            Storage::delete($media->file_path . $media->file_name);
            $media->delete();
            $result = array('Success' => true, 'Message' => 'yükleme başarılı');
            return response()->json('image null');
        } else {
            $result = array('Success' => false, 'Message' => 'Böyle bir media bulunmamaktadır');
            return response()->json($result);
        }
    }

    public function websiteslist()
    {
        $data = Website::with(['durum', 'ekleyen'])
            ->get();

        return $data;
    }

    public function searchTerms(Request $request, $website_slug, $slug)
    {



        $searchText = $slug;

        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();



        if ($website == null) {
            return response()->json('bad request');
        }


        $data = Post::with(['tags', 'durum', 'dil', 'ekleyen', 'onaylayan', 'kategori', 'media', 'post_type'])
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
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }
////////////////////////////////////////////Redirect/////////////////////////////////////////////////////
    public function seopanelredirecturlassign(Request $request)
    {
        $website_id = $request->input('website_id');
        $post_type_id = $request->input('post_type_id');
        $slug = $request->input('slug');
        $urldesc = $request->input('urldesc');
        $postSlug = $request->input('postslug');



        $linearray = explode('/', $slug);


        $count = count($linearray);
        $z = [];
        for ($i = 0; $i < $count; $i++) {

            array_push($z, Str::slug($linearray[$i]));
        }
        $a = implode("/", $z);

        $post = DB::table('posts')
            ->where('posts.website_id', '=', $website_id)
            ->where('posts.post_type_id', '=', $post_type_id)
            ->where('posts.status_id', '=', 2)
            ->where('posts.slug', '=', $postSlug)
            ->first();

        $redirecturlControl = DB::table('redirectedurl')
            ->where('redirectedurl.website_id', '=', $website_id)
            ->where('redirectedurl.post_type_id', '=', $post_type_id)
            ->where('redirectedurl.slug', '=', $a)
            ->first();

        $website_name = DB::table('websites')
            ->where('websites.id', '=', $website_id)
            ->first();

        if ($redirecturlControl != null) {

            $result = array('Success' => false, 'Message' => 'dublicate');

            return response()->json($result);
        }

        if ($post == null || $website_id  == null || $post_type_id  == null  || $postSlug == null) {
            return response()->json('bad request');
        }
        $redirecturl = RedirectUrl::create([
            'title' => $slug,
            'slug' => $a,
            'redirect_post_id' => $post->id,
            'website_id' => $website_id,
            'post_type_id' => $post_type_id,
            'urldesc' => $urldesc,
            'created_by' => Auth::id()
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }




    public function siteseoredirecturlassign(Request $request, $website_slug)  
    {

        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $website_id = $website->id;
        $post_type_id = $request->input('post_type_id');
        $slug = $request->input('slug');
        $urldesc = $request->input('urldesc');
        $postSlug = $request->input('postslug');



        $linearray = explode('/', $slug);


        $count = count($linearray);
        $z = [];
        for ($i = 0; $i < $count; $i++) {

            array_push($z, Str::slug($linearray[$i]));
        }
        $a = implode("/", $z);

        $post = DB::table('posts')
            ->where('posts.website_id', '=', $website_id)
            ->where('posts.post_type_id', '=', $post_type_id)
            ->where('posts.status_id', '=', 2)
            ->where('posts.slug', '=', $postSlug)
            ->first();

        $redirecturlControl = DB::table('redirectedurl')
            ->where('redirectedurl.website_id', '=', $website_id)
            ->where('redirectedurl.post_type_id', '=', $post_type_id)
            ->where('redirectedurl.slug', '=', $a)
            ->first();

        $website_name = DB::table('websites')
            ->where('websites.id', '=', $website_id)
            ->first();

        if ($redirecturlControl != null) {

            $result = array('Success' => false, 'Message' => 'dublicate');

            return response()->json($result);
        }

        if ($post == null || $website_id  == null || $post_type_id  == null  || $postSlug == null) {
            return response()->json('bad request');
        }
        $redirecturl = RedirectUrl::create([
            'title' => $slug,
            'slug' => $a,
            'redirect_post_id' => $post->id,
            'website_id' => $website_id,
            'post_type_id' => $post_type_id,
            'urldesc' => $urldesc,
            'created_by' => Auth::id()
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }

    public function seopanelredirecturlshow(Request $request, $id)
    {


        $redirecturlselect = DB::table('redirectedurl')
            ->leftjoin('posts', 'posts.id', '=', 'redirectedurl.redirect_post_id')
            ->select('redirectedurl.id', 'redirectedurl.redirect_post_id', 'redirectedurl.website_id', 'redirectedurl.post_type_id', 'redirectedurl.slug', 'redirectedurl.urldesc', 'posts.id as postId', 'posts.slug as postslug')
            ->where('redirectedurl.id', $id)
            ->first();


        if ($redirecturlselect != null) {
            return response()->json($redirecturlselect);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }


    public function siteseoredirecturlshow(Request $request, $website_slug, $id) 
    {

        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $redirecturlselect = DB::table('redirectedurl')
            ->leftjoin('posts', 'posts.id', '=', 'redirectedurl.redirect_post_id')
            ->select('redirectedurl.id', 'redirectedurl.redirect_post_id', 'redirectedurl.website_id', 'redirectedurl.post_type_id', 'redirectedurl.slug', 'redirectedurl.urldesc', 'posts.id as postId', 'posts.slug as postslug')
            ->where('redirectedurl.website_id', $website->id)
            ->where('redirectedurl.id', $id)
            ->first();


        if ($redirecturlselect != null) {
            return response()->json($redirecturlselect);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }

    }


    public function seopanelredirecturlList()
    {
        $data = RedirectUrl::with(['posts', 'post_type', 'websites', 'user'])
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json($data);
    }

    public function siteseoredirecturlList(Request $request, $website_slug)  
    {
        
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();
        $data = RedirectUrl::with(['posts', 'post_type', 'websites', 'user'])
            ->where('website_id',$website->id)
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json($data);
    }


    public function seopanelredirecturledit(Request $request, $id)
    {

        $redirecturlselect = RedirectUrl::where('id', $id)->first();


        if ($redirecturlselect != null) {

            $website_id = $request->input('website_id');
            $post_type_id = $request->input('post_type_id');
            $slug = $request->input('slug');
            $urldesc = $request->input('urldesc');
            $postSlug = $request->input('postslug');


            $linearray = explode('/', $slug);


            $count = count($linearray);
            $z = [];
            for ($i = 0; $i < $count; $i++) {

                array_push($z, Str::slug($linearray[$i]));
            }
            $a = implode("/", $z);


            $post = DB::table('posts')
                ->where('posts.website_id', '=', $website_id)
                ->where('posts.post_type_id', '=', $post_type_id)
                ->where('posts.status_id', '=', 2)
                ->where('posts.slug', '=', $postSlug)
                ->first();

            $website_name = DB::table('websites')
                ->where('websites.id', '=', $website_id)
                ->first();

            if ($post == null || $website_id  == null || $post_type_id  == null  || $postSlug == null) {
                return response()->json('bad request');
            }

            $redirecturlselect->update([
                'title' => $slug,
                'slug' => $a,
                'redirect_post_id' => $post->id,
                'website_id' => $website_id,
                'post_type_id' => $post_type_id,
                'urldesc' => $urldesc,
                'updated_by' => Auth::id()

            ]);
            $result = array('Success' => true);

            return response()->json($result);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }



    public function siteseoredirecturledit(Request $request, $website_slug, $id)  
    {

        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $redirecturlselect = RedirectUrl::where('id', $id)->first();


        if ($redirecturlselect != null) {

            $website_id = $website->id;
            $post_type_id = $request->input('post_type_id');
            $slug = $request->input('slug');
            $urldesc = $request->input('urldesc');
            $postSlug = $request->input('postslug');


            $linearray = explode('/', $slug);


            $count = count($linearray);
            $z = [];
            for ($i = 0; $i < $count; $i++) {

                array_push($z, Str::slug($linearray[$i]));
            }
            $a = implode("/", $z);


            $post = DB::table('posts')
                ->where('posts.website_id', '=', $website_id)
                ->where('posts.post_type_id', '=', $post_type_id)
                ->where('posts.status_id', '=', 2)
                ->where('posts.slug', '=', $postSlug)
                ->first();

            $website_name = DB::table('websites')
                ->where('websites.id', '=', $website_id)
                ->first();

            if ($post == null || $website_id  == null || $post_type_id  == null  || $postSlug == null) {
                return response()->json('bad request');
            }

            $redirecturlselect->update([
                'title' => $slug,
                'slug' => $a,
                'redirect_post_id' => $post->id,
                'website_id' => $website_id,
                'post_type_id' => $post_type_id,
                'urldesc' => $urldesc,
                'updated_by' => Auth::id()

            ]);
            $result = array('Success' => true);

            return response()->json($result);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }


    public function seopanelredirecturldelete(Request $request, $id)
    {
        $redirecturlselect = RedirectUrl::where('id', $id)->first();
        if ($redirecturlselect != null) {

            $redirecturlselect->delete();
            $result = array('Success' => true);

            return response()->json($result);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }


   public function siteseoredirecturldelete(Request $request, $website_slug, $id)
   {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();
       $redirecturlselect = RedirectUrl::where('id', $id)->where('website_id', $website->id)->first();

       if ($redirecturlselect != null) {

           $redirecturlselect->delete();
           $result = array('Success' => true);

           return response()->json($result);
       } else {
           $result = array('Success' => false);

           return response()->json($result);
       }
   }

///////////////////////////////////////////////Metalar //////////////////////////////////////////////////////////////////////////


    public function seopanelMetaList(Request $request)
    {

        $data = Post::with(['durum', 'dil', 'ekleyen', 'onaylayan', 'websites'])
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }


    public function siteseoMetaList(Request $request, $website_slug) 
    {  
        
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $data = Post::with(['durum', 'dil', 'ekleyen', 'onaylayan', 'websites'])
            ->where('website_id',$website->id)
            ->orderBy('id', 'DESC')
            ->get();

        return  response()->json($data);
    }

    public function seopanelMetaShow(Request $request, $id)
    {

        $potsMeta = DB::table('posts')
            ->leftjoin('users as u1', 'u1.id', '=', 'posts.created_by')
            ->leftjoin('users as u2', 'u2.id', '=', 'posts.approved_by')
            ->leftjoin('websites', 'websites.id', '=', 'posts.website_id')
            ->select(
                'posts.id',
                'posts.title',
                'posts.website_id',
                'posts.post_type_id',
                'posts.slug',
                'u1.name as ekleyen',
                'u2.name as onaylayan',
                'websites.title as website',
                'posts.meta_title',
                'posts.meta_desc',
                'posts.keywords'
            )
            ->where('posts.id', $id)
            ->first();


        if ($potsMeta != null) {
            return response()->json($potsMeta);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }





    public function siteseoMetaShow(Request $request, $website_slug, $id)  
    {


        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $potsMeta = DB::table('posts')
            ->leftjoin('users as u1', 'u1.id', '=', 'posts.created_by')
            ->leftjoin('users as u2', 'u2.id', '=', 'posts.approved_by')
            ->leftjoin('websites', 'websites.id', '=', 'posts.website_id')
            ->select(
                'posts.id',
                'posts.title',
                'posts.website_id',
                'posts.post_type_id',
                'posts.slug',
                'u1.name as ekleyen',
                'u2.name as onaylayan',
                'websites.title as website',
                'posts.meta_title',
                'posts.meta_desc',
                'posts.keywords'
            )
            ->where('posts.website_id', $website->id)
            ->where('posts.id', $id)
            ->first();


        if ($potsMeta != null) {
            return response()->json($potsMeta);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }

    public function seopanelMetaEdit(Request $request, $id)
    {
        $post = Post::where('id', $id)->first();


        $post->update([
            'meta_title'  => $request->input('meta_title'),
            'meta_desc' => $request->input('meta_desc'),
            'keywords' => $request->input('keywords'),
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }

   
    
    public function siteseoMetaEdit(Request $request, $website_slug, $id) 
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();
        $post = Post::where('id', $id)->where('website_id', $website->id)->first();
     
        $post->update([
            'meta_title'  => $request->input('meta_title'),
            'meta_desc' => $request->input('meta_desc'),
            'keywords' => $request->input('keywords'),
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }


    public function seopanelMetaClean(Request $request, $id)
    {
        $post = Post::where('id', $id)->first();

        $meta_title = null;
        $meta_desc = null;
        $keyword = null;

        $post->update([
            'meta_title'  => $meta_title,
            'meta_desc' => $meta_desc,
            'keywords' => $keyword,
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }

  

    public function siteseoMetaClean(Request $request,  $website_slug, $id) 
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $post = Post::where('id', $id)->where('website_id', $website->id)->first();

        $meta_title = null;
        $meta_desc = null;
        $keyword = null;

        $post->update([
            'meta_title'  => $meta_title,
            'meta_desc' => $meta_desc,
            'keywords' => $keyword,
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }

    public function seopanelTagList()
    {

        $data = DB::table('tags')
        ->leftjoin('websites', 'websites.id', '=', 'tags.website_id')
        ->leftjoin('posts_tags', 'posts_tags.tag_id', '=', 'tags.id')
        ->leftjoin('posts', 'posts.id', '=', 'posts_tags.post_id')
        ->leftjoin('post_types', 'post_types.id', '=', 'posts.post_type_id')
        ->select('tags.id','tags.tag_name','tags.slug','tags.meta_title','tags.meta_desc','tags.keywords','tags.created_at','tags.updated_at',
        'tags.website_id','posts.id as posts_id','posts.title as posts_title','posts.slug as posts_slug',
        'posts.summary as posts_summary','posts.content as posts_content','posts.status_id as posts_status_id','posts.language_id as posts_language_id'
        ,'posts.post_type_id as posts_post_type_id','posts.website_id as posts_website_id','websites.id as websites_id',
        'websites.title as websites_title','websites.slug as websites_slug','post_types.id as postType_id',
        'post_types.title as postType_title','post_types.slug as postType_slug')
        //->leftjoin('post_types', 'post_types.id', '=', 'tags.tag_id')
        ->orderBy('id', 'DESC')
        ->get();
      
        return response()->json($data);
    }



    public function siteseoTagList(Request $request, $website_slug)
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $data = DB::table('tags')
        ->leftjoin('websites', 'websites.id', '=', 'tags.website_id')
        ->leftjoin('posts_tags', 'posts_tags.tag_id', '=', 'tags.id')
        ->leftjoin('posts', 'posts.id', '=', 'posts_tags.post_id')
        ->leftjoin('post_types', 'post_types.id', '=', 'posts.post_type_id')
        ->select('tags.id','tags.tag_name','tags.slug','tags.meta_title','tags.meta_desc','tags.keywords','tags.created_at','tags.updated_at',
        'tags.website_id','posts.id as posts_id','posts.title as posts_title','posts.slug as posts_slug',
        'posts.summary as posts_summary','posts.content as posts_content','posts.status_id as posts_status_id','posts.language_id as posts_language_id'
        ,'posts.post_type_id as posts_post_type_id','posts.website_id as posts_website_id','websites.id as websites_id',
        'websites.title as websites_title','websites.slug as websites_slug','post_types.id as postType_id',
        'post_types.title as postType_title','post_types.slug as postType_slug')
        ->where('posts.website_id', $website->id)
        ->orderBy('id', 'DESC')
        ->get();
      
        return response()->json($data);
    }

    public function seopanelTagShow($id)
    {

        $Tagselect = Tag::with(['websites'])
            ->where('id', $id)
            ->first();
        if ($Tagselect != null) {

            $TagPostCollect = collect([[$Tagselect], [$Tagselect->posts]])->first();
            return response()->json($TagPostCollect);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }
    
  

    public function siteseoTagShow(Request $request, $website_slug,$id) 
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $Tagselect = Tag::with(['websites'])
            ->where('website_id',$website->id)
            ->where('id', $id)
            ->first();
        if ($Tagselect != null) {

            $TagPostCollect = collect([[$Tagselect], [$Tagselect->posts]])->first();
            return response()->json($TagPostCollect);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }

 
    public function seopanelTagInsert(Request $request)
    {
        $tagName = $request->input('tag_name');

        $newTag = Tag::create([
            'tag_name' => $tagName,
            'slug' => Str::slug($tagName),
            'website_id' => $request->input('website_id'),
            'meta_title' => $request->input('meta_title'),
            'meta_desc' => $request->input('meta_desc'),
            'keywords' => $request->input('keywords')
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }

 
    public function siteseoTagInsert(Request $request, $website_slug)  
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $tagName = $request->input('tag_name');

        $newTag = Tag::create([
            'tag_name' => $tagName,
            'slug' => Str::slug($tagName),
            'website_id' => $website->id,
            'meta_title' => $request->input('meta_title'),
            'meta_desc' => $request->input('meta_desc'),
            'keywords' => $request->input('keywords')
        ]);

        $result = array('Success' => true);

        return response()->json($result);
    }

    public function seopanelTagEdit(Request $request, $id)
    {

        $updateTag = Tag::where('id', $id)->first();
        $tagName = $request->input('tag_name');
        $updateTag->update([
            'tag_name' => $tagName,
            'slug' => Str::slug($tagName),
            'website_id' => $request->input('website_id'),
            'meta_title' => $request->input('meta_title'),
            'meta_desc' => $request->input('meta_desc'),
            'keywords' => $request->input('keywords')
        ]);


        $result = array('Success' => true);

        return response()->json($result);
    }


    public function siteseoTagEdit(Request $request, $website_slug, $id)
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $updateTag = Tag::where('id', $id)->first();
        $tagName = $request->input('tag_name');
        $updateTag->update([
            'tag_name' => $tagName,
            'slug' => Str::slug($tagName),
            'website_id' => $website->id,
            'meta_title' => $request->input('meta_title'),
            'meta_desc' => $request->input('meta_desc'),
            'keywords' => $request->input('keywords')
        ]);


        $result = array('Success' => true);

        return response()->json($result);
    }


    public function seopanelTagDelete($id)
    {

        $deleteTag = Tag::where('id', $id)->first();

        if ($deleteTag != null) {

            $deleteTag->delete();

            $result = array('Success' => true);

            return response()->json($result);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }

 
    public function siteseoTagDelete(Request $request, $website_slug,$id) //yeni////////////////////////////////////////////////////////////////////////
    {
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $deleteTag = Tag::where('id', $id)->where('website_id',$website->id)->first();

        if ($deleteTag != null) {

            $deleteTag->delete();

            $result = array('Success' => true);

            return response()->json($result);
        } else {
            $result = array('Success' => false);

            return response()->json($result);
        }
    }

    public function fileAllList()
    {
        $documents = DB::table('media')
            ->where('media_type_id', '=', 2)
            ->get();

        return response()->json($documents);
    }

    public function fileList($website_slug)
    {
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $documents = DB::table('media')
            ->where('media_type_id', '=', 2)
            ->where('website_id', '=', $website->id)
            ->get();

        return response()->json($documents);
    }

    public function filePostList($id)
    {
        $documents = DB::table('media')
            ->where('media_type_id', '=', 2)
            ->where('post_id', $id)
            ->get();

        return response()->json($documents);
    }

    public function addfilewithcreatepost(Request $request)
    {

        $posts = DB::table('posts')->orderBy('id', 'DESC')->first();

        $website = DB::table('websites')
            ->where('websites.id', '=', $posts->website_id)
            ->first();

        if ($selectfiled = $request->hasFile('file')) {
            $media = new Media($request->input());
            $selectfile = $request->file('file');
            $originalfullName = $selectfile->getClientOriginalName();
            $fileSlice = explode(".", $originalfullName);
            $fileBasicName = $fileSlice[0] . time() . '.' . $fileSlice[1];
            $justSliceBasicName = explode(".", $fileBasicName);
            $justSliceNewName = Str::slug($justSliceBasicName[0]);
            $justSliceExt = $fileSlice[1];
            $fileFullNewName = $justSliceNewName . "." . $justSliceExt;
            // $fileNewName =$fileName.'.'.request()->file->getClientOriginalExtension();
            $filesize = $this->bytesToHuman($request->file('file')->getSize());
            $selectfile->move(storage_path('app/public/hotlink-ok/documents/' . $website->slug . date('/yy/m/')), $fileFullNewName);
            $filepath = 'public/hotlink-ok/documents/' . $website->slug . date('/yy/m/');
            $url = 'uploads/hotlink-ok/documents/' . $website->slug . date('/yy/m/') . $fileFullNewName;
            $media->title = $justSliceNewName; //resmin adını alır
            $media->file_name = $fileFullNewName;
            $media->file_path = $filepath;
            $media->filesize = $filesize;
            $media->post_id = $posts->id;
            $media->url = $url;
            $media->media_type_id = 2;
            $media->fileformat = $fileSlice[1];
            $media->website_id = $website->id;
            $media->created_by = Auth::id();
            $media->save(); //resmin adını database aktarır

            $result = array('Success' => true, 'Message' => 'file uploaded');

            return response()->json($result);
        } else {
            $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın doc,docx,txt,pdf,pptx,zıp,ıso,xls,xlsx uzantılı olduğuna dikkat edin.!');

            return response()->json($result);
        }
    }


    public function addfile(Request $request, $id)
    {

        $posts = Post::where('id', $id)->first();

        $website = DB::table('websites')
            ->where('websites.id', '=', $posts->website_id)
            ->first();

        if ($selectfiled = $request->hasFile('file')) {
            $media = new Media($request->input());
            $selectfile = $request->file('file');
            $originalfullName = $selectfile->getClientOriginalName();
            $fileSlice = explode(".", $originalfullName);
            $fileBasicName = $fileSlice[0] . time() . '.' . $fileSlice[1];
            $justSliceBasicName = explode(".", $fileBasicName);
            $justSliceNewName = Str::slug($justSliceBasicName[0]);
            $justSliceExt = $fileSlice[1];
            $fileFullNewName = $justSliceNewName . "." . $justSliceExt;
            // $fileNewName =$fileName.'.'.request()->file->getClientOriginalExtension();
            $filesize = $this->bytesToHuman($request->file('file')->getSize());
            $selectfile->move(storage_path('app/public/hotlink-ok/documents/' . $website->slug . date('/yy/m/')), $fileFullNewName);
            $filepath = 'public/hotlink-ok/documents/' . $website->slug . date('/yy/m/');
            $url = 'uploads/hotlink-ok/documents/' . $website->slug . date('/yy/m/') . $fileFullNewName;
            $media->title = $justSliceNewName; //resmin adını alır
            $media->file_name = $fileFullNewName;
            $media->file_path = $filepath;
            $media->filesize = $filesize;
            $media->post_id = $posts->id;
            $media->url = $url;
            $media->media_type_id = 2;
            $media->fileformat = $fileSlice[1];
            $media->website_id = $website->id;
            $media->created_by = Auth::id();
            $media->save(); //resmin adını database aktarır

            $result = array('Success' => true, 'Message' => 'file uploaded');

            return response()->json($result);
        } else {
            $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın doc,docx,txt,pdf,pptx,zıp,ıso,xls,xlsx uzantılı olduğuna dikkat edin.!');

            return response()->json($result);
        }
    }


    public function addfilePanel(Request $request)
    {
        $post_id = $request->input('post_id');

        $website_id = $request->input('website_id');

        $posts = Post::where('id', $post_id)->first();

        if ($post_id != null) {
            $website = DB::table('websites')
                ->where('websites.id', '=', $posts->website_id)
                ->first();


            if ($selectfiled = $request->hasFile('file')) {
                $media = new Media($request->input());
                $selectfile = $request->file('file');
                $originalfullName = $selectfile->getClientOriginalName();
                $fileSlice = explode(".", $originalfullName);
                $fileBasicName = $fileSlice[0] . time() . '.' . $fileSlice[1];
                $justSliceBasicName = explode(".", $fileBasicName);
                $justSliceNewName = Str::slug($justSliceBasicName[0]);
                $justSliceExt = $fileSlice[1];
                $fileFullNewName = $justSliceNewName . "." . $justSliceExt;
                // $fileNewName =$fileName.'.'.request()->file->getClientOriginalExtension();
                $filesize = $this->bytesToHuman($request->file('file')->getSize());
                $selectfile->move(storage_path('app/public/hotlink-ok/documents/' . $website->slug . date('/yy/m/')), $fileFullNewName);
                $filepath = 'public/hotlink-ok/documents/' . $website->slug . date('/yy/m/');
                $url = 'uploads/hotlink-ok/documents/' . $website->slug . date('/yy/m/') . $fileFullNewName;
                $media->title = $justSliceNewName; //resmin adını alır
                $media->file_name = $fileFullNewName;
                $media->file_path = $filepath;
                $media->filesize = $filesize;
                $media->post_id = $posts->id;
                $media->url = $url;
                $media->media_type_id = 2;
                $media->fileformat = $fileSlice[1];
                $media->website_id = $website->id;
                $media->created_by = Auth::id();
                $media->save(); //resmin adını database aktarır

                $result = array('Success' => true, 'Message' => 'file uploaded');

                return response()->json($result);
            } else {
                $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın doc,docx,txt,pdf,pptx,zıp,ıso,xls,xlsx uzantılı olduğuna dikkat edin.!');

                return response()->json($result);
            }
        } else if ($website_id != null) {
            $website = DB::table('websites')
                ->where('websites.id', '=', $website_id)
                ->first();


            if ($selectfiled = $request->hasFile('file')) {
                $media = new Media($request->input());
                $selectfile = $request->file('file');
                $originalfullName = $selectfile->getClientOriginalName();
                $fileSlice = explode(".", $originalfullName);
                $fileBasicName = $fileSlice[0] . time() . '.' . $fileSlice[1];
                $justSliceBasicName = explode(".", $fileBasicName);
                $justSliceNewName = Str::slug($justSliceBasicName[0]);
                $justSliceExt = $fileSlice[1];
                $fileFullNewName = $justSliceNewName . "." . $justSliceExt;
                // $fileNewName =$fileName.'.'.request()->file->getClientOriginalExtension();
                $filesize = $this->bytesToHuman($request->file('file')->getSize());
                $selectfile->move(storage_path('app/public/hotlink-ok/documents/' . $website->slug . date('/yy/m/')), $fileFullNewName);
                $filepath = 'public/hotlink-ok/documents/' . $website->slug . date('/yy/m/');
                $url = 'uploads/hotlink-ok/documents/' . $website->slug . date('/yy/m/') . $fileFullNewName;
                $media->title = $justSliceNewName; //resmin adını alır
                $media->file_name = $fileFullNewName;
                $media->file_path = $filepath;
                $media->filesize = $filesize;
                $media->url = $url;
                $media->media_type_id = 2;
                $media->fileformat = $fileSlice[1];
                $media->website_id = $website->id;
                $media->created_by = Auth::id();
                $media->save(); //resmin adını database aktarır

                $result = array('Success' => true, 'Message' => 'file uploaded');

                return response()->json($result);
            } else {
                $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın doc,docx,txt,pdf,pptx,zıp,ıso,xls,xlsx uzantılı olduğuna dikkat edin.!');

                return response()->json($result);
            }
        } else {
            if ($selectfiled = $request->hasFile('file')) {
                $media = new Media($request->input());
                $selectfile = $request->file('file');
                $originalfullName = $selectfile->getClientOriginalName();
                $fileSlice = explode(".", $originalfullName);
                $fileBasicName = $fileSlice[0] . time() . '.' . $fileSlice[1];
                $justSliceBasicName = explode(".", $fileBasicName);
                $justSliceNewName = Str::slug($justSliceBasicName[0]);
                $justSliceExt = $fileSlice[1];
                $fileFullNewName = $justSliceNewName . "." . $justSliceExt;
                // $fileNewName =$fileName.'.'.request()->file->getClientOriginalExtension();
                $filesize = $this->bytesToHuman($request->file('file')->getSize());
                $selectfile->move(storage_path('app/public/hotlink-ok/documents/panel/' . date('/yy/m/')), $fileFullNewName);
                $filepath = 'public/hotlink-ok/documents/panel/' . date('/yy/m/');
                $url = 'uploads/hotlink-ok/documents/panel/' . date('/yy/m/') . $fileFullNewName;
                $media->title = $justSliceNewName; //resmin adını alır
                $media->file_name = $fileFullNewName;
                $media->file_path = $filepath;
                $media->url = $url;
                $media->filesize = $filesize;
                $media->media_type_id = 2;
                $media->website_id = 0;
                $media->fileformat = $fileSlice[1];
                $media->created_by = Auth::id();
                $media->save(); //resmin adını database aktarır

                $result = array('Success' => true, 'Message' => 'file uploaded panel');

                return response()->json($result);
            } else {
                $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın doc,docx,txt,pdf,pptx,zıp,ıso,xls,xlsx uzantılı olduğuna dikkat edin.!');

                return response()->json($result);
            }
        }
    }


    public static function bytesToHuman($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }


    public function deletefile(Request $request, $id)
    {

        $media = Media::where('id', $id)->first();

        if ($media != null) {
            Storage::delete($media->file_path . $media->file_name);
            $media->delete();
            $result = array('Success' => true, 'Message' => 'Dosya silindi');
            return response()->json($result);
        } else {
            return 'hata';
        }
    }
}
