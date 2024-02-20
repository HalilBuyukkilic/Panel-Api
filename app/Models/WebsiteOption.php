<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteOption extends Model
{
    protected $table='website_options';
    
    protected $fillable=[
        'id',
        'website_id',
        'name',
        'value',
            
    ];
}
