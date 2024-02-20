<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table='permissions';

    protected $fillable=[
                          'permission',
                          'aciklama',
                          'slug',
                        ];

                        
        public function roles()
        {
            return $this->belongsToMany(Role::class,  'permission_role','role_ID','permission_ID');
        }

}
