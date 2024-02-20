<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'id',
        'title',
        'slug',
        'summary',
        'content',
        'category_id',
        'meta_title',
        'meta_desc',
        'keywords',
        'tags',
        'status_id',
        'language_id',
        'post_type_id',
        'website_id',
        'approved_by',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'published_at'
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class,  'posts_tags', 'post_id', 'tag_id');
    }

    public function durum()
    {
        return $this->hasOne(Durum::class, 'id', 'status_id');
    }

    public function dil()
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }

    public function ekleyen()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function onaylayan()
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }
    public function kategori()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function media()
    {
        return $this->hasOne(Media::class, 'id', 'media_id');
    }

    public function post_type()
    {
        return $this->hasOne(PostType::class, 'id', 'post_type_id');
    }

    public function websites()
    {
        return $this->hasOne(Website::class, 'id', 'website_id');
    }
}
