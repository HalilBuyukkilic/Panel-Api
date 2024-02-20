<?php

namespace App\Models;

use App\Models\Website;
use Illuminate\Database\Eloquent\Model;

class WebsiteMenu extends Model
{
    protected $table = 'website_menus';

    protected $fillable = [
        'id',
        'website_id',
        'parent_id',
        'title',
        'description',
        'icon',
        'link',
        'menu_order',
        'created_at',
        'updated_at',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id', 'id');
    }
}
