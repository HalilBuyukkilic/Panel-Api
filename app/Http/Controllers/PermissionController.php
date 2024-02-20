<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class PermissionController extends Controller
{
    public function index()
    {

        $permissions = Permission::all();
        return  response()->json($permissions);
    }

    public function assignPermissionNewRoles(Request $request)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }


        if (in_array('roles_assignPermission', $stack)) {
            $data = Request::get("is_ok");
            $limit = array_push($data);

            $array = Request::get("is_ok") + array_fill(0, $limit, 0);

            $roles = Role::orderBy('created_at', 'DESC')->first();

            $permissions = Permission::where('permission', 'kullanici')->first();

            $roles->permissions()->sync($array);

            return  response()->json($roles->permissions);
        } else {
            return response()->json('unauthorized');
        }
    }

    public function assignUpdatePermissions(Request $request, $slug)
    {

        $u = Auth::user();
        $u->load('roles.permissions');
        $roleLimit = count($u->roles);
        $stack = array();
        $permission = Permission::all();
        //  return $u->roles[1]->permissions;

        for ($x = 0; $x < $roleLimit; $x++) {
            $permissionLimit = count($u->roles[$x]->permissions);

            for ($y = 0; $y < $permissionLimit; $y++) {
                array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
            }
        }


        if (in_array('roles_assignUpdatePermission', $stack)) {

            $data = Request::get("is_ok");

            $limit = array_push($data);

            $array = Request::get("is_ok") + array_fill(0, $limit, 0);

            $roles = Role::where('slug', $slug)->first();
            $permissions = Permission::where('permission', 'kullanici')->first();

            $roles->permissions()->sync($array);

            return  response()->json($roles->permissions);
        } else {
            return response()->json('unauthorized');
        }
    }

    public function roleListPermissions(Request $request, $slug)
    {
        $roleName = Role::where('slug', $slug)->first();
        $roles = Role::where('slug', $slug)->first();
        $roles->permissions();

        return  response()->json(['roles' => $roleName, 'permissions' => $roles->permissions]);
    }
}
