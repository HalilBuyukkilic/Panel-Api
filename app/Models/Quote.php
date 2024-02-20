<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $table='teklif';
    
    protected $fillable=[
                          'website_id',
                          'fullname',
                          'email',
                          'telefon',
                          'product',
                          'note',
                          'slug'
                        ];


    
  public function websites()
  {
      return $this->hasOne(Website::class,'id', 'website_id' );
  }                       
}
