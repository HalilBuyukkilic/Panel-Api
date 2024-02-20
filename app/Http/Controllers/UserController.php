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
use Validator;
use App\Models\Role;
use App\Models\Permission;
use App\Models\perasisImage;
use App\Models\User;

class UserController extends Controller
{
    public $successStatus = 200;


    public function login()
    {

        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::User();
            $success['token1'] = $user->id;
            $success['onay'] = $user->durum_ID;
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $request->merge([
            //'slug'=>str_slug($request->username)
            'slug' => Str::slug($request->username)
        ]);
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
        $success['username'] =  $user->username;
        $success['telefon'] =  $user->telefon;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function details()
    {

        $user = Auth::User();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function controlUser()
    {
        $user = Auth::User();
        $data = DB::table('users')
            ->leftjoin('image', 'image.avatarID', '=', 'users.id')
            ->select('users.id', 'users.username', 'users.name', 'users.email', 'users.password', 'users.telefon', 'users.slug', 'users.website_ID')
            ->where('users.slug', '=', $user->slug)
            ->get();

        return response()->json($data);
    }



    public function adminConfirm()
    {


        $role = Role::all();

        $user = Auth::user();
        $roleLimit = count($user->roles);
        $role = $user->roles;
        $stack = array();
        for ($x = 0; $x < $roleLimit; $x++) {

            array_push($stack, $role[$x]['role']);
        }

        //return $stack;
        if (in_array('admin', $stack)) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }













    public function index()
    {


        $u = Auth::user();
        // $u->load('roles.permissions');

        // $roleLimit=count($u->roles);  
        // $stack = array();
        // $permission=Permission::all();      
        // // return $u->roles[1]->permissions;

        // for($x = 0; $x < $roleLimit; $x++)
        // {  
        //     $permissionLimit=count($u->roles[$x]->permissions);

        //     for($y = 0; $y < $permissionLimit; $y++)
        //     {
        //         array_push($stack,$u->roles[$x]->permissions[$y]['permission']);

        //     }

        // }



        if (true) { //in_array('users_list',$stack)){

            $data = DB::table('users')
                ->leftjoin('users as u1', 'u1.id', '=', 'users.user_ID')
                ->leftjoin('users as u2', 'u2.id', '=', 'users.onay_ID')
                ->leftjoin('durum', 'durum.id', '=', 'users.durum_ID')
                //->leftjoin('image','image.avatarID','=','users.id')
                ->select(
                    'users.id',
                    'users.username',
                    'users.name',
                    'users.email',
                    'users.telefon',
                    'users.slug',
                    'users.created_at',
                    'users.updated_at',
                    'u1.username as ekleyen',
                    'u2.username as Onaylayan',
                    'durum.aciklama2'
                )
                ->orderBy('created_at', 'DESC')
                ->get();
            return  response()->json($data);
        } else {
            return response()->json('unauthorized');
        }
        /*
            $users = User::with(['roles','image' => function($q){
                $q;
                }])->get();

            return $users;

        */
    }



    public function show($slug)
    {
        

         $user=User::where('slug',$slug)->first();
        // $roles=$user->roles;

        // $u = Auth::user();
        // $u->load('roles.permissions');
        // return $u;
        // $roleLimit = count($u->roles);
        // $stack = array();
        // $permission = Permission::all();
        // //  return $u->roles[1]->permissions;

        // for ($x = 0; $x < $roleLimit; $x++) {
        //     $permissionLimit = count($u->roles[$x]->permissions);

        //     for ($y = 0; $y < $permissionLimit; $y++) {
        //         array_push($stack, $u->roles[$x]->permissions[$y]['permission']);
        //     }
        // }

         
        if (true){ //in_array('users_show', $stack)) {
            if ($user != null) {

                $data = DB::table('users')
                    ->leftjoin('image', 'image.avatarID', '=', 'users.id')
                    ->select(
                        'users.username',
                        'users.name',
                        'users.email',
                        'users.password',
                        'users.telefon',
                        'users.slug',
                        'users.durum_ID',
                        'users.website_ID',
                        'users.created_at as eklenmeTarihi',
                        'users.updated_at as guncellenmeTarihi',
                        'image.imageUrl'
                    )
                    ->where('users.slug', '=', $user->slug)
                    ->get();
                //$result = array('user' => $data, 'roles' => $roles);
                return response()->json($data);
            } else {
                $result = array('Success' => false, 'Message' => 'bu id`ye ait kullanıcı bulunmamaktadır,silinmiş veya hiç eklenmemiş olabilir');
                return response()->json($result);
            }
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

        if (in_array('users_post', $stack)) {

            try {

                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|email',
                    'password' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['valideterror']);
                }
                
                $request->merge([

                    'slug' => Str::slug($request->username)
                ]);
                
                $user = User::create([
                    'username' => $request->input('username'),
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'telefon' => $request->input('telefon'),
                    'website_ID' => $request->input('website_ID'),
                    'slug' => $request->input('slug'),
                    'durum_ID' => $request->input('durum_ID'),
                    'user_ID' => Auth::id()
                ]);


                $result = array('Success' => true, 'Message' => 'user eklendi');

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


    public function addAvatar(Request $request)
    {
        $user = DB::table('users')->orderBy('created_at', 'DESC')->first();

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

        if (in_array('users_post', $stack)) {

            if ($file = $request->hasFile('image')) {


                request()->validate([

                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

                ]);

                $avatarName = $user->slug;
                $avatarID = $user->id;
                $image = new perasisImage($request->input());
                $file = $request->file('image');
                $avatarUrlName = $avatarName . '.' . request()->image->getClientOriginalExtension();
                $file->move(storage_path('app/public/images/perasis/avatar'), $avatarUrlName); //resmi storage kaydeder
                $image->imageName = $avatarName;
                $image->imageUrl = $avatarUrlName;
                $image->avatarID = $avatarID;
                $image->sayfa_ID = 1;
                $image->tag_title = request()->tag_title;
                $image->tag_alt = request()->tag_alt;
                $image->save(); //resmin adını database aktarır

                $result = array('Success' => true, 'Message' => 'avatar uploaded');

                return response()->json($result);
            } else {
                $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın jpeg,png,jpg,gif,svg uzantılı olduğuna dikkat edin.!');

                return response()->json($result);
            }
        } else {
            return response()->json('unauthorized');
        }
    }

    public function changeAvatar(Request $request, $slug)
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

        if (in_array('users_put', $stack)) {
            $user = User::where('slug', $slug)->first();
            $userID = $user->id;
            $image = perasisImage::where('sayfa_ID', 1)->where('avatarID', $userID)->first();


            if ($image != null) {



                if ($request->hasFile('image')) {
                    request()->validate([

                        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

                    ]);
                    if ($file = $request->hasFile('image')) {

                        Storage::delete('public/images/perasis/avatar/' . $image->imageUrl);

                        $file = $request->file('image');

                        $fileName = $user->slug;
                        $imageNewName = $fileName . time() . '.' . request()->image->getClientOriginalExtension();
                        $file->move(storage_path('app/public/images/perasis/avatar'), $imageNewName); //resmi storage kaydeder

                        $image->imageName = $fileName; //resmin adını alır
                        $image->imageUrl = $imageNewName;
                        $image->tag_title = request()->tag_title;
                        $image->tag_alt = request()->tag_alt;
                        $image->save(); //resmin adını database aktarır



                        $result = array('Success' => true, 'Message' => 'image updated');

                        return response()->json($result);
                    }
                } else {
                    $imageOldName = $image->imageUrl;
                    $fileNewName = $user->slug;
                    $imageExt = explode(".", $imageOldName);
                    $mergeNewNameAndExt = $fileNewName . time() . '.' . $imageExt[1];
                    $rawContent = Storage::get('public/images/perasis/avatar/' . $imageOldName);
                    Storage::put('public/images/avatar/' . $mergeNewNameAndExt, $rawContent);
                    if ($imageOldName != $mergeNewNameAndExt) {
                        Storage::delete('public/images/perasis/avatar/' . $image->imageUrl);
                        $image->imageName = $fileNewName; //resmin adını alır
                        $image->imageUrl = $mergeNewNameAndExt;
                        $image->tag_title = request()->tag_title;
                        $image->tag_alt = request()->tag_alt;
                        $image->save();
                    }
                    //resmin adını database aktarır

                    $result = array('Success' => true, 'Message' => 'image updated');

                    return response()->json($result);
                }
            } else {


                if ($file = $request->hasFile('image')) {
                    $image = new perasisImage($request->input());

                    $file = $request->file('image');

                    // $fileName = $file->getClientOriginalName() ;
                    $avatarName = $user->slug;
                    $avatarID = $user->id;
                    $avatarUrlName = $avatarName . '.' . request()->image->getClientOriginalExtension();
                    $file->move(storage_path('app/public/images/perasis/avatar'), $avatarUrlName); //resmi storage kaydeder
                    $image->imageName = $avatarName;
                    $image->imageUrl = $avatarUrlName;
                    $image->avatarID = $avatarID;
                    $image->sayfa_ID = 1;
                    $image->tag_title = request()->tag_title;
                    $image->tag_alt = request()->tag_alt;
                    $image->save();
                    $result = array('Success' => true, 'Message' => 'image uploaded');

                    return response()->json($result);
                } else {
                    $result = array('Success' => false, 'Message' => 'lütfen seçilen dosyanın jpeg,png,jpg,gif,svg uzantılı olduğuna dikkat edin.!');

                    return response()->json($result);
                }
            }
        } else {
            return response()->json('unauthorized');
        }
    }


    public function confirm()
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

        if (in_array('users_confirm', $stack)) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }


    public function blocked()
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

        if (in_array('users_blocked', $stack)) {
            return response()->json(true);
        } else {
            return response()->json(false);
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

        if (true){ //in_array('users_put', $stack)) {

            try {
                $user = User::where('slug', $slug)->first();

            
                $userForCondition = User::where('slug', $slug)->first();

                if ($user != null) {
                    $request->merge([
                        'slug' => Str::slug($request->username)
                    ]);

                   
                   if ($user->password != $request->input('password')) {
                       
                       $user->update([
                        'username' => $request->input('username'),
                        'name' => $request->input('name'),
                        'email' => $request->input('email'),
                        'password' => bcrypt($request->input('password')),
                        'telefon' => $request->input('telefon'),
                        'website_ID' => $request->input('website_ID'),
                        'durum_ID' => $request->input('durum_ID'),
                        'slug' => $request->input('slug'),
                        'onay_ID' => Auth::id()
                      ]);
                   }else{
                      
                    $user->update([
                        'username' => $request->input('username'),
                        'name' => $request->input('name'),
                        'email' => $request->input('email'),
                        'telefon' => $request->input('telefon'),
                        'website_ID' => $request->input('website_ID'),
                        'durum_ID' => $request->input('durum_ID'),
                        'slug' => $request->input('slug'),
                        'onay_ID' => Auth::id()
                    ]);
                   }
                    
                  
                   
                    $result = array('Success' => true, 'Message' => 'kişi güncellendi', $userForCondition, Str::slug($request->username));

                    return response()->json($result);
                } else {

                    $result = array('Success' => false, 'Message' => 'Güncelleme olurken bir hata oluştu.Güncellemek istediğiniz kişi silinmiş olabilir');

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

        if (in_array('users_delete', $stack)) {
            $user = User::where('slug', $slug)->first();
            $image = perasisImage::where('sayfa_ID', 1)->where('imageName', $slug)->first();
            if ($user == null) {
                $result = array('Success' => true, 'Message' => 'Bu üye silinmiş veya hiç eklenmemiş olabilir');

                return response()->json($result);
            } else {


                if ($image == null) {
                    $user->delete();
                    $result = array('Success' => true, 'Message' => 'Üye silindi');

                    return response()->json($result);
                } else {
                    if ($image != null) {
                        DB::table('image')
                            ->where('imageName', $slug)
                            ->where('image.sayfa_ID', '=', 1)
                            ->delete();
                        Storage::delete('public/images/perasis/avatar/' . $image->imageUrl);
                        $user->delete();
                        $result = array('Success' => true, 'Message' => 'Üye silindi');

                        return response()->json($result);
                    }
                }
            }
        } else {
            return 'kullanıcı silme yekisi yok';
        }
    }
}
