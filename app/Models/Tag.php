<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps = false;
    protected $table='tags';
    
    protected $fillable=[
      'id',
      'tag_name',
      'slug',
      'website_id',
      'meta_title', 
      'meta_desc', 
      'keywords' 
    ];



  
      public function websites()
      {
          return $this->hasOne(Website::class,'id', 'website_id' );
      } 

      public function posts()
      {
         return $this->belongsToMany(Post::class,  'posts_tags','tag_id', 'post_id');
      }

    


}
