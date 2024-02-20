<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class perasisImage extends Model
{
    protected $table='image';
    
    protected $fillable=[
                          'imageName',
                          'imageUrl',
                          'image_ID',
                          'sayfa_ID'
                        
                        ];


     public function users()
     {
          return   $this->hasMany(User::class ,'id','avatarID');
     }
}
