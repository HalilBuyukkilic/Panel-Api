<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB; 
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;   
use Carbon\Carbon;
use Spatie\Sitemap\Sitemap;
use App\Models\Category;
use App\Models\Tag;
class SitemapGenerate{

    public static function CreateFile($website_slug){
        
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        if($website == null)
            return 'Bu site bulunamadı.!';

        $posts= Post::with(['post_type' , 'kategori'])
            ->where('website_id', '=', $website->id)
            ->where('status_id', '=', 2)
            //->whereNotIn('post_type_id',[])
            ->where('post_type_id', '!=', 4)
            ->get();

            
        $categories = Category::whereHas('posts', function($q) 
            {
                $q->where('posts.status_id', '=', 2);
               
            })
            ->where('post_type_id', '!=', 7)
            ->where('website_id', '=',  $website->id)
            ->get();    
  
        $tags = DB::table('tags')
            ->where('tags.website_id', '=', $website->id)
           // ->where('post_type_id', '=', 2)
            ->join('posts_tags', 'posts_tags.tag_id', '=', 'tags.id')
            ->join('posts', 'posts.id', '=', 'posts_tags.post_id')
            ->select('tags.tag_name','tags.slug','tags.created_at','posts.published_at')
            ->distinct('tags.tag_name')
            ->get();     
 
       
        if($posts == null || count($posts) == 0) {
            return 'Bu siteye ait link verisi bulunamadı.!';
        }else{
            
            $sitemap = SitemapGenerator::create($website->url)
            ->getSitemap();
         
            

        
             foreach ($posts as $post) {
                # code...
                $url = '';
                if($post->post_type->url_type == 1)
                    $url = $website->url . '/' . $post->slug;
                else if($post->post_type->url_type == 2)
                    if($post->post_type->id == 9){
                        $url = $website->url . '/' . $post->post_type->slug ;
                    }else{
                        $url = $website->url . '/' . $post->post_type->slug . '/'. $post->slug;
                    }
                else if($post->post_type->url_type == 3)
                    $url = $website->url . '/' . $post->post_type->slug . '/'. $post->kategori->slug . '/'. $post->slug;

                $sitemap = $sitemap->add(Url::create($url)
                    ->setLastModificationDate($post->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(1.0));
              }


              foreach ($categories as $category) {
                # code...
                $sitemap = $sitemap->add(Url::create($website->url.'/category/'.$category->slug)
                    ->setLastModificationDate(Carbon::create($category->created_at ->toDateTimeString()))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(1.0));
              }

              foreach ($tags as $tag) {
                # code...
                $sitemap = $sitemap->add(Url::create($website->url .'/tag/' . $tag->slug)
                    ->setLastModificationDate(Carbon::create($tag->created_at))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(1.0));
              }

          
                
            $sitemap->writeToFile($website->target_folder . '/sitemap.xml');
            return 'Sitemap generated';
        }

    }

}