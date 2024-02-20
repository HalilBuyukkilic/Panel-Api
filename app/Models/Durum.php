<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Durum extends Model
{
    protected $table='durum';

    protected $fillable=[
        'id',
        'durum',
        'aciklama',
        'updated_at',
        'user_ID'
        
        ];

//    public function yorum(){

//       return   $this->hasMany(perasisYorum::class ,'durum_ID');
//    }
 
//    public function haber(){

//        return   $this->hasMany(perasisHaber::class ,'durum_ID');
//    }
//    public function makale(){

//        return   $this->hasMany(perasisMakale::class ,'durum_ID');
//    } 
   
               
}
