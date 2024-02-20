<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Role;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
class adminGuard
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
        $role=Role::all();
      
        $user = Auth::user(); 
        $roleLimit=count($user->roles);
        $role=$user->roles;
        $stack = array();
        for($x = 0; $x < $roleLimit; $x++)
        {
        
            array_push($stack,$role[$x]['role']);
            
        }

        //return $stack;
        if(in_array('admin',$stack)){
            return $next($request);
        }else{
            return 'giriş reddedildi';
        }
   
    }
    
}
