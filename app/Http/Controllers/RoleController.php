<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
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


        if (in_array('roles_list', $stack)) {
            $roles = Role::all();
            $roles->load('permissions');

            return response()->json($roles);
        } else {
            return response()->json('unauthorized');
        }



        // return  response()->json($roles);
    }

    public function assignRolesNewUser(Request $request)
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


        if (in_array('user_assignRole', $stack)) {
            $data = Request::get("is_ok");
            $limit = array_push($data);

            $array = Request::get("is_ok") + array_fill(0, $limit, 0);

            $user = User::orderBy('created_at', 'DESC')->first();
            $role = Role::where('role', 'kullanici')->first();
            $user->roles()->sync($array);

            return  response()->json($user->roles);
        } else {
            return response()->json('unauthorized');
        }
    }

    public function assignUpdateRoles(Request $request, $slug)
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


        if (in_array('user_assignUpdateRole', $stack)) {
            $data = Request::get("is_ok");
            $limit = array_push($data);

            $array = Request::get("is_ok") + array_fill(0, $limit, 0);

            $user = User::where('slug', $slug)->first();
            $role = Role::where('role', 'kullanici')->first();
            $user->roles()->sync($array);

            return  response()->json($user->roles);
        } else {
            return response()->json('unauthorized');
        }
    }

    public function userListRoles(Request $request, $slug)
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


        if (in_array('users_show', $stack)) {
            $user = User::where('slug', $slug)->first();
            $user->roles();
            return  response()->json($user->roles);
        } else {
            return response()->json('unauthorized');
        }
    }

    public function store(Request $request)
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


        if (in_array('roles_post', $stack)) {
            try {
                $request->merge([

                    'slug' => Str::slug($request->role)
                ]);

                $kategori = Role::create([
                    'role' => $request->input('role'),
                    'aciklama' => $request->input('aciklama'),
                    'slug' => $request->input('slug'),
                    'user_ID' => Auth::id()
                ]);

                $result = array('Success' => true, 'Message' => 'role eklendi');

                return response()->json($result);
            } catch (QueryException $e) {
                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    $result = array('DublicateError' => 'duplicate');
                    return $result;
                }
            }
        } else {
            return response()->json('unauthorized');
        }
    }



    public function update(Request $request, $slug)
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


        if (in_array('roles_put', $stack)) {

            try {
                $role = Role::where('slug', $slug)->first();
                $roleForCondition = Role::where('slug', $slug)->first();


                if ($role != null) {
                    $request->merge([
                        'slug' => Str::slug($request->role)
                    ]);



                    $role->update([
                        'role' => $request->input('role'),
                        'aciklama' => $request->input('aciklama'),
                        'slug' => $request->input('slug'),
                    ]);

                    $result = array('Success' => true, 'Message' => 'Role güncellendi', Str::slug($request->role), $roleForCondition);

                    return response()->json($result);
                } else {

                    $result = array('Success' => false, 'Message' => 'Güncelleme olurken bir hata oluştu.Güncellemek istediğiniz Role silinmiş olabilir');

                    return response()->json($result);
                }
            } catch (QueryException $e) {
                $error_code = $e->errorInfo[1];
                if ($error_code == 1062) {
                    $result = array('DublicateError' => 'duplicate');
                    return $result;
                }
            }
        } else {
            return response()->json('unauthorized');
        }
    }


    public function destroy(Request $request, $slug)
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


        if (in_array('roles_delete', $stack)) {

            $role = Role::where('slug', $slug)->first();
            if ($role == null) {
                $result = array('Success' => true, 'Message' => 'Bu Role silinmiş veya hiç eklenmemiş olabilir');

                return response()->json($result);
            } else {

                $role->delete();
                $result = array('Success' => true, 'Message' => 'Role silindi');
                return response()->json($result);
            }
        } else {
            return response()->json('unauthorized');
        }
    }
}
