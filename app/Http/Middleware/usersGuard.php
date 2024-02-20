<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
class usersGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         
        $u = \Auth::user(); 
        $u->load('roles.permissions'); 
        $roleLimit=count($u->roles);  
        $stack = array();
        $permission=Permission::all();      
        //  return $u->roles[1]->permissions;

        for($x = 0; $x < $roleLimit; $x++)
        {  
            $permissionLimit=count($u->roles[$x]->permissions);
            
            for($y = 0; $y < $permissionLimit; $y++)
            {
                array_push($stack,$u->roles[$x]->permissions[$y]['permission']);
            }
    
        }

        if(in_array('users',$stack)){
            return $next($request); 
        }else{
            return 'giriş reddedildi';
        }
            

        
    }
}
