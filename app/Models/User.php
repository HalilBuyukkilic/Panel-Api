<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens, Notifiable;
    //  protected $connection = 'mysql';
     public $incrementing = false;
  
  
      protected static function boot()
      {
          parent::boot();
      
          static::creating(function ($model) {
              $model->{$model->getKeyName()} = \Ramsey\Uuid\Uuid::uuid4()->toString();
          });
      }




    protected $table='users';
  
    protected $fillable = [
        'username','name', 'email', 'password','telefon','slug','user_ID','onay_ID','durum_ID','website_ID'
    ];


    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class,  'role_user','user_ID','role_ID');
    }
    
    public function image(){

        return   $this->hasMany( perasisImage::class ,'avatarID','id');
    }
   
    public function yorum(){

     return   $this->hasMany( perasisYorum::class ,'user_ID','onay_ID');
    }

   
    public function haber(){

        return   $this->hasMany(perasisHaberler::class ,'user_ID');
    }
    
    public function makale(){

        return   $this->hasMany(perasisMakale::class ,'user_ID');
    }

}
