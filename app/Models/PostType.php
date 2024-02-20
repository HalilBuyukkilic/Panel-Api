<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostType extends Model
{
    protected $table='post_types';
    
    protected $fillable=[
                          'id',
                          'title',
                          'title_plural',
                          'slug',
                          'website_id',
                          'seo_enabled',
                          'media_enabled',
                          'summary_enabled',
                          'tag_enabled',
                          'url_enabled',
                          'author_enabled',
                          'language_enabled',
                          'category_enabled',
                          'alldelete_enabled',
                          'url_type',
                          'slug-en',
                          'slug-ar',
                          'folder-path'
                 
    ];
}
