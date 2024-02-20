<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Role;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
class ManageFilter
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
      
   
        return $next($request);



        /*

          $role=perasisRole::all();
      
        $user = Auth::user(); 
        $roleLimit=count($user->roles);
        $role=$user->roles;
        $stack = array();
        for($x = 0; $x < $roleLimit; $x++)
        {
        
            array_push($stack,$role[$x]['role']);
            
        }

        //return $stack;

    

        if(count($stack)==0){
            return 'giris reddedildi';
        }else if(in_array('uye',$stack)){
            if(count($stack)!=1){
                return $next($request);
            }else{
                return 'giris reddedildi';
            }
        }else{
            return $next($request);
        }


        */
    }
}
