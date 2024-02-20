<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectUrl extends Model
{
    protected $table='redirectedurl';
    
    protected $fillable=[
        'id',
        'title',
        'urldesc',
        'slug',
        'redirect_post_id',
        'website_id',
        'post_type_id',
        'created_by',
        'updated_by'
  
    ]; 

    public function posts()
    {
        return $this->hasMany(Post::class, 'id', 'redirect_post_id');
    }

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
        return $this->hasOne(User::class, 'id', 'created_by' );
    }
}
