<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table='dil';

    protected $fillable=[
        'id',
        'dil',
        'aciklama',
        'slug'
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
