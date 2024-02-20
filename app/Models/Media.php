<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table='media';
    
    protected $fillable=[
        'id',
        'title',
        'file_name',
        'file_path',
        'url',
        'content',
        'dimensions',
        'fileformat',
        'tag_title',
        'tag_alt',
        'post_id',
        'media_type_id',
        'website_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
                        
    ];
}
