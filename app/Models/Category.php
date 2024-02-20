<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table='kategori';
    
    protected $fillable=[
                          'kategori',
                          'aciklama',
                          'post_type_id',
                          'website_id',
                          'user_ID',
                          'slug',
                          'meta_title', 
                          'meta_desc', 
                          'keywords'    
                        ];

  

    public function post_type()
    {
        return $this->hasOne(PostType::class,'id', 'post_type_id' );
    }

    public function websites()
    {
        return $this->hasOne(Website::class,'id', 'website_id' );
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_ID' );
    }                    

    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id', 'id' );
    }
    
                    
}
