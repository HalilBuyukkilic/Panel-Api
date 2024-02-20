<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder; 
use Illuminate\Support\Str;
use Validator;
use App\Models\Category; 
use App\Utils\SitemapGenerate;

class KategoriController extends Controller
{


    public function list(Request $request, $website_slug){
       
    
       
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $data=DB::table('kategori')
        ->leftjoin('users as u1','u1.id','=','kategori.user_ID')
        ->leftjoin('dil','dil.id','=','kategori.dil_ID')
        ->leftjoin('post_types','post_types.id','=','kategori.post_type_id')
        ->select( 'kategori.id','kategori.kategori','kategori.aciklama','kategori.slug','post_types.title as type','kategori.created_at',
        'kategori.updated_at','u1.username as ekleyen','dil.dil')
        ->orderBy('kategori.id', 'DESC')
        ->where('kategori.website_id', '=', $website->id)
        ->get();

        return  response()->json($data);

    }

    public function categorylistPostType(Request $request, $website_slug,$post_type_slug){

        $post_type = DB::table('post_types')
        ->where('post_types.slug', '=', $post_type_slug)
        ->first();
   
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();

        $data=DB::table('kategori')
            ->where('kategori.website_id', '=', $website->id)
            ->join('posts', 'posts.category_id', '=', 'kategori.id')
            ->where('posts.post_type_id', '=', $post_type->id)
            ->select('kategori.kategori','kategori.slug')
            ->distinct('kategori.kategori')
            ->get();

      return  response()->json($data);
    }

    public function forTypelist(Request $request, $website_slug,$post_type_slug){
       
        $post_type = DB::table('post_types')
            ->where('post_types.slug', '=', $post_type_slug)
            ->first();
       
        $website = DB::table('websites')
            ->where('websites.slug', '=', $website_slug)
            ->first();

        $data=DB::table('kategori')
            ->leftjoin('users as u1','u1.id','=','kategori.user_ID')
            ->leftjoin('dil','dil.id','=','kategori.dil_ID')
            ->leftjoin('post_types','post_types.id','=','kategori.post_type_id')
            ->select( 'kategori.id','kategori.kategori','kategori.aciklama','kategori.slug','post_types.title as type','kategori.created_at',
            'kategori.updated_at','u1.username as ekleyen','dil.dil')
            ->orderBy('id', 'DESC')
            ->where('kategori.website_id', '=', $website->id)
            ->where('kategori.post_type_id', '=', $post_type->id)
            ->get();

        return  response()->json($data);

    }

    public function kategoriTypeList(){
       
        $data=DB::table('post_types')
   
        ->select( 'post_types.id','post_types.title','post_types.slug')
        ->get();

        return  response()->json($data);
    }


    public function insert(Request $request,$website_slug){


        $post_type_id=$request->input('post_type_id');



        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();
         
        if($post_type_id == null || $website == null){
            return response()->json('bad request');
        }

        try{
            $request->merge([
               
                'slug'=> Str::slug($request->kategori)
             ]);
    
            $kategori=Category::create([
                'kategori' =>$request->input('kategori'),
                'aciklama' =>$request->input('aciklama'),
                'post_type_id' => $post_type_id,
                'website_id' => $website->id,
                'slug' => $request->input('slug'),
                'user_ID' => Auth::id()
            ]);
           // SitemapGenerate::CreateFile($website_slug);
                $result = array('Success' => true , 'Message' => 'kategori eklendi','slug'=>$kategori->slug);
            
                return response()->json($result);
        }catch (QueryException $e){
                $error_code = $e->errorInfo[1];
                if($error_code == 1062){
                    $result = array('DublicateError' => 'duplicate' );
                    return $result;
                }
        }

    }
    
    public function show($website_slug,$slug){

             
                $website = DB::table('websites')
                ->where('websites.slug', '=', $website_slug)
                ->first();

                $kategori=Category::where('slug',$slug)->get();
             
                if($kategori != null){
                
                   return response()->json($kategori);
                }else{
                    $result = array('Success' => false , 'Message' => 'Böyle bir kategori bulunmamaktadır,silinmiş veya hiç eklenmemiş olabilir');
                    return response()->json($result);
                }
     
        
    }

    public function update(Request $request,$website_slug,$slug){

        
        $kategoriCondition=Category::where('slug',$slug)->first();

        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();
       
        $websiteID=$website->id;
         
        $kategori=Category::where('slug',$slug)->where('website_id',$websiteID)->first();

    
            try{
              
                if($kategori != null){
                    $request->merge([
                        'slug'=> Str::slug($request->kategori)
                    ]);
        
            
        
                    $kategori->update([
                        'kategori' =>$request->input('kategori'),
                        'aciklama' =>$request->input('aciklama'),
                        'post_type_id' =>$request->input('post_type_id'),
                        'website_id' =>$websiteID,
                        'user_ID' => Auth::id(),
                        'slug' => $request->input('slug'),
                
                    ]);
                   // SitemapGenerate::CreateFile($website_slug);
                
                    $result = array('Success' => true , 'Message' => 'Kategori güncellendi',$kategoriCondition->slug);
                    
                    return response()->json($result);
                }else{
                    
                    $result = array('Success' => false , 'Message' => 'Güncelleme olurken bir hata oluştu.Güncellemek istediğiniz Kategori silinmiş olabilir');
                    
                    return response()->json($result);
                        }
                }
                catch (QueryException $e){
                    $error_code = $e->errorInfo[1];
                    if($error_code == 1062){
                        $result = array('DublicateError' => 'duplicate' );
                        return $result;
                    }
                }
        
    }

    public function delete(Request $request,$website_slug,$slug){
       
        $website = DB::table('websites')
        ->where('websites.slug', '=', $website_slug)
        ->first();
       
        $websiteID=$website->id;
        $kategori=Category::where('slug',$slug)->where('website_id',$websiteID)->first();
       // $kategori_id=$kategori->id;
     
        if($kategori==null){
                $result = array('Success' => true , 'Message' => 'Bu Kategori silinmiş veya hiç eklenmemiş olabilir');     
                return response()->json($result);
        }else{
                
                   $kategori->delete();
                 //  SitemapGenerate::CreateFile($website_slug);        
                   $result = array('Success' => true , 'Message' => 'Kategori silindi' );
                    
                  return response()->json($result);
             
                
                
         }

    

    }


    public function seopanelkategoriList(){
        $data = Category::with(['post_type', 'user','websites'])
        ->orderBy('id', 'DESC')
        ->get();

        return  response()->json($data);
    }

    public function seopanelkategorishow(Request $request,$id){
        $kategoriselect= DB::table('kategori')
        ->leftjoin('websites','websites.id','=','kategori.website_id')
        ->leftjoin('post_types','post_types.id','=','kategori.post_type_id')
        ->leftjoin('users','users.id','=','kategori.user_ID')
        ->select('kategori.id','kategori.kategori','kategori.aciklama','kategori.post_type_id','kategori.meta_title','kategori.meta_desc','kategori.keywords',
        'kategori.slug','kategori.website_id','websites.title','post_types.title','users.name as ekleyen')
        ->where('kategori.id', $id)
        ->first();


        if($kategoriselect != null) {
            return response()->json($kategoriselect);
            
        }else{
            $result = array('Success' => false  );
        
            return response()->json($result);
        }
    }

    public function seopanelkategoriinsert(Request $request){
            $kategori = $request->input('kategori');

            $newkategori = Category::create([
                'kategori' => $kategori,
                'aciklama' => $request->input('aciklama'),
                'slug' => Str::slug($kategori),
                'website_id' => $request->input('website_id'),
                'post_type_id' =>$request->input('post_type_id'),
                'dil_ID' =>$request->input('dil'),
                'meta_title' =>$request->input('meta_title'),
                'meta_desc' =>$request->input('meta_desc'),
                'keywords' =>$request->input('keywords'),
                'user_ID' => Auth::id()
            ]);
        // SitemapGenerate::CreateFile($website_slug);
        $result = array('Success' => true);
            
        return response()->json($result);
    }

    public function seopanelkategoriedit(Request $request,$id){

        $updatekategori=Category::where('id',$id)->first();

        $updatekategori->update([
            'kategori' => $request->input('kategori'),
            'aciklama' => $request->input('aciklama'),
            'slug' => Str::slug($request->input('kategori')),
            'website_id' => $request->input('website_id'),
            'post_type_id' =>$request->input('post_type_id'),
            'dil_ID' =>$request->input('dil'),
            'meta_title' =>$request->input('meta_title'),
            'meta_desc' =>$request->input('meta_desc'),
            'keywords' =>$request->input('keywords')
        ]);
       // SitemapGenerate::CreateFile($website_slug);

        $result = array('Success' => true);
            
        return response()->json($result);
    }

    public function seopanelkategoriDelete($id){
    
        $deletekategori=Category::where('id',$id)->first();

        if($deletekategori != null) {

            $deletekategori->delete(); 
            $result = array('Success' => true );
            //SitemapGenerate::CreateFile($website_slug);
            return response()->json($result);
        }else{
            $result = array('Success' => false);
        
            return response()->json($result);
        }
    }


}
