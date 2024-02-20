<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $table = 'websites';

    protected $fillable = [
        'title',
        'slug',
        'url'

    ];

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

    public function media()
    {
        return $this->hasOne(Media::class, 'id', 'media_id');
    }

    public function menus()
    {
        return $this->hasMany(WebsiteMenu::class, 'website_id', 'id');
    }
}
