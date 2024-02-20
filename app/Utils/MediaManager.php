<?php

namespace App\Utils;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder; 
use Illuminate\Support\Str;
use App\Models\Post;   
use App\Models\Media;
use App\Models\Website;
class MediaManager{

    public const IMAGE = 1;
    public const DOCUMENT = 2;
    public const VIDEO = 3;

    private const IMAGE_STORAGE_PATH = 'app/public/images/';
    private const DOCUMENT_STORAGE_PATH = 'app/public/documents/';

    private const IMAGE_STORAGE_DELETE_PATH='public/images/';

    //private const URL_PATH='storage/app/public/images/';
    private const URL_PATH='uploads/images/'; //resimlerin gelmesini sağlayan linkin storage mi yoksa uploads olmasını sağlayan kod

    public static function CreateMedia($params){
        
        if(!isset($params['media_type']))
            return ['Success' => false, 'Message' => 'Error: media type must be defined.'];
            
        if(!isset($params['website_id']) && !isset($params['website_slug']))
            return ['Success' => false, 'Message' => 'Error: website must be defined.'];

        if(!isset($params['website_slug']))
        {
            $website = Website::where('id',$params['website_id'])->first();

            if(!$website)
                return ['Success' => false, 'Message' => 'Error: website must be defined.'];

            $params['website_slug'] = $website->slug;
        }

        if($params['media_type'] == MediaManager::IMAGE || $params['media_type'] == MediaManager::DOCUMENT)
        {

            if(!isset($params['file']))
                return ['Success' => false, 'Message' => 'Error: file must be defined.'];
            if(!isset($params['file_name']))
                return ['Success' => false, 'Message' => 'Error: file name must be defined.'];
            if(!isset($params['file_extension']))
                return ['Success' => false, 'Message' => 'Error: file extension must be defined.'];

            $file = $params['file'];
            $file_name = $params['file_name'].time();
            $file_extension = $params['file_extension'];

            $file_path = $params['media_type'] == MediaManager::IMAGE ? MediaManager::IMAGE_STORAGE_PATH : MediaManager::DOCUMENT_STORAGE_PATH;
            $file_path = $file_path . $params['website_slug'] . date('/yy/m/');

            $file_path2 = $params['media_type'] == MediaManager::IMAGE ? MediaManager::IMAGE_STORAGE_DELETE_PATH : MediaManager::DOCUMENT_STORAGE_PATH;
            $file_path2 = $file_path2 . $params['website_slug'] . date('/yy/m/');
              

            $url = $params['media_type'] == MediaManager::IMAGE ? MediaManager:: URL_PATH : MediaManager::DOCUMENT_STORAGE_PATH;
            $url = $url . $params['website_slug'] . date('/yy/m/');
            //dosya mevcut mu kontrol
            if(file_exists(storage_path($file_path . $file_name . '.' . $file_extension)))
            {
                $counter = 2;
                while(file_exists(storage_path($file_path . $file_name . '-' . $counter . '.' . $file_extension)))
                {
                    $counter++;
                }
                $file_name = $file_name . '-' . $counter;
            }

            $file_name = $file_name . '.' . $file_extension;
            
            $url = $url.$file_name;

            $file->move(storage_path($file_path), $file_name);

            $media = new Media();
            $media->title = $params['title'];
            $media->file_name = $file_name;
            $media->file_path = $file_path2;
            $media->url = $url;
            $media->tag_title = $params['tag_title'];
            $media->tag_alt = $params['tag_alt'];
            $media->media_type_id = $params['media_type'];
            $media->website_id = $params['website_id'];
            $media->post_id = $params['post_id'];
            $media->created_by = Auth::id();
            $media->dimensions=$params['dimensions'];
            $media->fileformat=$params['fileformat'];
            $media->filesize=$params['filesize'];
            $media->save();

            return ['Success' => true , 'Media' => $media];

        }
        else if($params['media_type'] == MediaManager::VIDEO)
        {
            $media = new Media();
            $media->title = $params['title'];
            $media->url = $params['url'];
            $media->content = $params['content'];
            $media->tag_title = $params['tag_title'];
            $media->tag_alt = $params['tag_alt'];
            $media->media_type_id = $params['media_type'];
            $media->website_id = $params['website_id'];
            $media->created_by = Auth::id();

            $media->save();

            return ['Success' => true , 'Media' => $media];
        }

    }



    public static function UpdateMedia($params){
       
       
        if(!isset($params['media_type']))
            return ['Success' => false, 'Message' => 'Error: media type must be defined.'];
            
        if(!isset($params['website_id']) && !isset($params['website_slug']))
            return ['Success' => false, 'Message' => 'Error: website must be defined.'];






            if(!isset($params['website_slug']))
            {
                $website = Website::where('id',$params['website_id'])->first();
    
                if(!$website)
                    return ['Success' => false, 'Message' => 'Error: website must be defined.'];
    
                $params['website_slug'] = $website->slug;
            }
    
            if($params['media_type'] == MediaManager::IMAGE || $params['media_type'] == MediaManager::DOCUMENT)
            {
    
                if(!isset($params['file']))
                    return ['Success' => false, 'Message' => 'Error: file must be defined.'];
                if(!isset($params['file_name']))
                    return ['Success' => false, 'Message' => 'Error: file name must be defined.'];
                if(!isset($params['file_extension']))
                    return ['Success' => false, 'Message' => 'Error: file extension must be defined.'];
    
                $file = $params['file'];
                $file_name = $params['file_name'].time();
                $file_extension = $params['file_extension'];
    
                $file_path = $params['media_type'] == MediaManager::IMAGE ? MediaManager::IMAGE_STORAGE_PATH : MediaManager::DOCUMENT_STORAGE_PATH;
                $file_path = $file_path . $params['website_slug'] . date('/yy/m/');
    
                $file_path2 = $params['media_type'] == MediaManager::IMAGE ? MediaManager::IMAGE_STORAGE_DELETE_PATH : MediaManager::DOCUMENT_STORAGE_PATH;
                $file_path2 = $file_path2 . $params['website_slug'] . date('/yy/m/');
                  
    
                $url = $params['media_type'] == MediaManager::IMAGE ? MediaManager:: URL_PATH : MediaManager::DOCUMENT_STORAGE_PATH;
                $url = $url . $params['website_slug'] . date('/yy/m/');

               //güncellemede eski image silme
                $medias = Media::where('post_id',$params['post_id'])->where('website_id', $params['website_id'])->first();
                if($medias != null){
                    Storage::delete($medias->file_path.$medias->file_name);
                }
  
                 
                //dosya mevcut mu kontrol
                if(file_exists($file_path . $file_name . '.' . $file_extension))
                {
                    $counter = 2;
                    while(file_exists($file_path . $file_name . '-' . $counter . '.' . $file_extension))
                    {
                        $counter++;
                    }
                    $file_name = $file_name . '-' . $counter;
                }
    
                $file_name = $file_name . '.' . $file_extension;
                
                $url = $url.$file_name;
                if($medias == null){

                    $file->move(storage_path($file_path), $file_name);
                    $media = new Media();
                    $media->title = $params['title'];
                    $media->file_name = $file_name;
                    $media->file_path = $file_path2;
                    $media->url = $url;
                    $media->tag_title = $params['tag_title'];
                    $media->tag_alt = $params['tag_alt'];
                    $media->media_type_id = $params['media_type'];
                    $media->website_id = $params['website_id'];
                    $media->post_id = $params['post_id'];
                    $media->created_by = Auth::id();
                    $media->dimensions=$params['dimensions'];
                    $media->fileformat=$params['fileformat'];
                    $media->filesize=$params['filesize'];
                    $media->save();

                    return ['Success' => true , 'Media' => $media];

                }else if($params['ifchange']==$params['orginal']){
                    
                    $file->move(storage_path($file_path), $file_name);
        
                    $media = Media::where('id',$params['media_id'])->where('website_id', $params['website_id'])->first();
                    $media->title = $params['title'];
                    $media->file_name = $file_name;
                    $media->file_path = $file_path2;
                    $media->url = $url;
                    $media->tag_title = $params['tag_title'];
                    $media->tag_alt = $params['tag_alt'];
                    $media->media_type_id = $params['media_type'];
                    $media->website_id = $params['website_id'];
                    $media->post_id = $params['post_id'];
                    $media->dimensions=$params['dimensions'];
                    $media->fileformat=$params['fileformat'];
                    $media->filesize=$params['filesize'];
                    $media->created_by = Auth::id();
        
                    $media->save();
        
                    return ['Success' => true , 'Media' => $media];
               }else{
                    $media = Media::where('id',$params['media_id'])->where('website_id', $params['website_id'])->first();
                    Storage::delete($media->file_path.$media->file_name);

                    $file->move(storage_path($file_path), $file_name);
            
                  
                    $media->title = $params['title'];
                    $media->file_name = $file_name;
                    $media->file_path = $file_path2;
                    $media->url = $url;
                    $media->tag_title = $params['tag_title'];
                    $media->tag_alt = $params['tag_alt'];
                    $media->media_type_id = $params['media_type'];
                    $media->website_id = $params['website_id'];
                    $media->post_id = $params['post_id'];
                    $media->dimensions=$params['dimensions'];
                    $media->fileformat=$params['fileformat'];
                    $media->filesize=$params['filesize'];
                    $media->created_by = Auth::id();
        
                    $media->save();
                   
                    return ['Success' => true , 'Media' => $media];
               }
            
    
            }
            else if($params['media_type'] == MediaManager::VIDEO)
            {
                $media = new Media();
                $media->title = $params['title'];
                $media->url = $params['url'];
                $media->content = $params['content'];
                $media->tag_title = $params['tag_title'];
                $media->tag_alt = $params['tag_alt'];
                $media->media_type_id = $params['media_type'];
                $media->website_id = $params['website_id'];
                $media->created_by = Auth::id();
    
                $media->save();
    
                return ['Success' => true , 'Media' => $media];
            }
    

    }



    public static function DeleteMedia($params){
     
        if(!$params['media_id'] && !$params['post_id'])
            return ['Success' => false, 'Message' => 'Error: media id must be defined.'];
            
        if(!$params['website_id'])
            return ['Success' => false, 'Message' => 'Error: website must be defined.'];

        if($params['id'])
        {
            $media = Media::where('id',$params['media_id'])->where('website_id', $params['website_id'])->first();

            if(!$media)
                return ['Success' => false, 'Message' => 'Error: media not found.'];
             
            if(($params['media_type'] == MediaManager::IMAGE || $params['media_type'] == MediaManager::DOCUMENT) && file_exists('storage/images/faturaport/2020/01/'.$media->file_name))
               
                Storage::delete($media->file_path.$media->file_name);
                $media->delete();

            return ['Success' => true, 'Message' => 'Media deleted succesfully.'];
        }
        else if($params['post_id'])
        {
            
            $medias = Media::where('post_id',$params['post_id'])->where('website_id', $params['website_id'])->get();
            return  $medias->file_path;
            if(!$medias || count($medias) == 0)
                return ['Success' => false, 'Message' => 'Error: no media found for related post.'];
              
            foreach ($medias as $media) 
            {
              //  if(($params['media_type'] == MediaManager::IMAGE || $params['media_type'] == MediaManager::DOCUMENT) && file_exists($media->file_path))
                    Storage::delete($media->file_path);

                $media->delete();
            }

            return ['Success' => true, 'Message' => 'Media deleted succesfully.'];
        }

    }

}
