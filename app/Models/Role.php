<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table='roles';

    protected $fillable=[
                          'role',
                          'aciklama',
                          'slug',
                          'user_ID',
                        ];



    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user','role_ID','user_ID');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role','role_ID','permission_ID');
    }
    

    
}
