<?php

use Illuminate\Http\Request;
use Spatie\Sitemap\SitemapGenerator;
use Illuminate\Support\Facades\DB; 

Route::get('{website_slug}/sitemap.xml', 'SitemapController@createSitemap');

Route::post('login', 'UserController@login');

Route::post('register', 'UserController@register');

Route::get('userlistRoles/{slug}','RoleController@userListRoles');

Route::get('{website_slug}/sitemap','PostsController@trySitemap');

Route::group(['middleware' => 'auth:api','cors'], function(){

    Route::post('details', 'UserController@details');
    
    Route::get('controlUser','UserController@controlUser');

    Route::group(['prefix'=>'manager','middleware' => ['ManageFilter']], function () {

        Route::get('adminConfirm','UserController@adminConfirm');
        
        Route::prefix('menus')->group(function () {

            Route::get('/','MenuController@index');
                
            Route::get('/{websiteId}','MenuController@getWebsiteMenus');
                
            Route::post('add','MenuController@add');
        
            Route::post('update/{menuId}','MenuController@update');
        
            Route::post('delete/{menuId}','MenuController@delete');

            Route::get('get/{menuId}','MenuController@get');

        });

        Route::prefix('users')->group(function () {

            Route::get('/','UserController@index');
                
            Route::get('show/{slug}','UserController@show');

            Route::get('checkList/{slug}','UserController@onayList');
        
            Route::post('post','UserController@store');
        
            Route::put('update/{slug}','UserController@update');
        
            Route::delete('delete/{slug}','UserController@destroy');

            Route::get('confirm','UserController@confirm');

            Route::get('blocked','UserController@blocked');

            Route::post('addavatar','UserController@addAvatar');

            Route::post('putavatar/{id}','UserController@changeAvatar');

        });

        Route::prefix('roles')->group(function () {

            Route::get('/','RoleController@index');
        
            Route::get('show/{slug}','RoleController@show');
            
            Route::post('assignRoles','RoleController@assignRolesNewUser');

            Route::post('assignUpdateRoles/{slug}','RoleController@assignUpdateRoles');
            
            Route::post('post','RoleController@store');
        
            Route::put('update/{slug}','RoleController@update');
        
            Route::delete('delete/{slug}','RoleController@destroy');

            Route::prefix('permissions')->group(function () {

                Route::get('/','PermissionController@index');

                Route::post('assignPermissions','PermissionController@assignPermissionNewRoles');

                Route::get('show/{slug}','PermissionController@roleListPermissions');

                Route::post('assignUpdatePermissions/{slug}','PermissionController@assignUpdatePermissions');
            
                Route::put('update/{slug}','PermissionController@update');
            
                Route::delete('delete/{slug}','PermissionController@destroy');  
                        
            });
        
        });

        Route::prefix('durum')->group(function () {

            Route::get('/','DurumController@index');
        
            Route::get('show/{slug}','DurumController@show');

            Route::get('checkList/{slug}','DurumController@onayList');
        
            Route::post('post','DurumController@store');
        
            Route::put('update/{slug}','DurumController@update');
        
            Route::delete('delete/{slug}','DurumController@destroy');
        
        });

        Route::prefix('{website_slug}/users')->group(function () {

            Route::get('/','UserController@index');
                
            Route::get('show/{slug}','UserController@show');

            Route::get('checkList/{slug}','UserController@onayList');
        
            Route::post('post','UserController@store');
        
            Route::put('update/{slug}','UserController@update');
        
            Route::delete('delete/{slug}','UserController@destroy');

            Route::get('confirm','UserController@confirm');

            Route::get('blocked','UserController@blocked');

            Route::post('addavatar','UserController@addAvatar');

            Route::post('putavatar/{id}','UserController@changeAvatar');

        });

        Route::prefix('{website_slug}/roles')->group(function () {

            Route::get('/','RoleController@index');
        
            Route::get('show/{slug}','RoleController@show');
            
            Route::post('assignRoles','RoleController@assignRolesNewUser');

            Route::post('assignUpdateRoles/{slug}','RoleController@assignUpdateRoles');
            
            Route::post('post','RoleController@store');
        
            Route::put('update/{slug}','RoleController@update');
        
            Route::delete('delete/{slug}','RoleController@destroy');

            Route::prefix('permissions')->group(function () {

                Route::get('/','PermissionController@index');

                Route::post('assignPermissions','PermissionController@assignPermissionNewRoles');

                Route::get('show/{slug}','PermissionController@roleListPermissions');

                Route::post('assignUpdatePermissions/{slug}','PermissionController@assignUpdatePermissions');
            
                Route::put('update/{slug}','PermissionController@update');
            
                Route::delete('delete/{slug}','PermissionController@destroy');  
                        
            });
        
        });

        Route::prefix('{website_slug}/durum')->group(function () {

            Route::get('/','DurumController@index');
        
            Route::get('show/{slug}','DurumController@show');

            Route::get('checkList/{slug}','DurumController@onayList');
        
            Route::post('post','DurumController@store');
        
            Route::put('update/{slug}','DurumController@update');
        
            Route::delete('delete/{slug}','DurumController@destroy');
        
        });
        
        Route::prefix('{website_slug}/blog')->group(function () {

            Route::get('/','PostsController@list')->defaults('post_type_id', '2');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '2');

            Route::get('tag/{tag_name}','PostsController@tag')->defaults('post_type_id', '2');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '2');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '2');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '2');

        });

        Route::prefix('{website_slug}/sayfalar')->group(function () {

            Route::get('/','PostsController@list')->defaults('post_type_id', '2');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '2');

            Route::get('tag/{tag_name}','PostsController@tag')->defaults('post_type_id', '2');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '2');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '2');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '2');

        });

        Route::prefix('{website_slug}/hizmetler')->group(function () {

            
            Route::get('/','PostsController@list')->defaults('post_type_id', '3');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '3');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '3');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '3');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '3');
        
        });

        Route::prefix('{website_slug}/clients')->group(function () {

            Route::get('/','PostsController@list')->defaults('post_type_id', '4');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '4');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '4');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '4');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '4');
        
        });

        Route::prefix('{website_slug}/products')->group(function () {

            Route::get('/','PostsController@list')->defaults('post_type_id', '5');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '5');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '5');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '5');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '5');
        });

        Route::prefix('{website_slug}/updates')->group(function () {

            Route::get('/','PostsController@list')->defaults('post_type_id', '6');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '6');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '6');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '6');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '6');
        
        });

        Route::prefix('{website_slug}/support')->group(function () {

            Route::get('/','PostsController@list')->defaults('post_type_id', '7');
        
            Route::get('show/{slug}','PostsController@show')->defaults('post_type_id', '7');
        
            Route::post('post','PostsController@insert')->defaults('post_type_id', '7');
        
            Route::post('update/{slug}','PostsController@update')->defaults('post_type_id', '7');
        
            Route::delete('delete/{slug}','PostsController@delete')->defaults('post_type_id', '7');
        
        });

        Route::prefix('{website_slug}/teklif')->group(function () {

            Route::get('/','TeklifController@index');
        
            Route::get('show/{id}','TeklifController@show');

            Route::delete('delete/{id}','TeklifController@delete');
        
        });

        Route::prefix('teklifAll')->group(function () {

            Route::get('/','TeklifController@indexteklifAll');
        
            Route::get('show/{id}','TeklifController@showteklifPanel');

            Route::delete('delete/{id}','TeklifController@deleteteklifPanel');
        
        });
        
        Route::prefix('{website_slug}/kategori')->group(function () {

            Route::get('/','KategoriController@list');

            Route::get('/{post_type_slug}','KategoriController@forTypelist');
            
            Route::get('show/{slug}','KategoriController@show');
        
            Route::post('post','KategoriController@insert');
        
            Route::put('update/{slug}','KategoriController@update');
        
            Route::delete('delete/{slug}','KategoriController@delete');
        
        });
        
   

        Route::prefix('sites')->group(function () {

            Route::get('/','PostsControllerV2@websiteslist');
        
        });
        
        Route::prefix('dashboard')->group(function () {

            Route::get('SitesCount','DashboardController@SitesCount');
        
            Route::get('ContentsCount','DashboardController@ContentsCount');
        
            Route::get('MediasCount','DashboardController@MediasCount');
        
            Route::get('UsersCount','DashboardController@UsersCount');
        
        });
        
        Route::prefix('{website_slug}/dashboard')->group(function () {

            Route::get('SitesCount','DashboardWebsiteController@SitesCount');
        
            Route::get('ContentsCount','DashboardWebsiteController@ContentsCount');
        
            Route::get('MediasCount','DashboardWebsiteController@MediasCount');
        
            Route::get('UsersCount','DashboardWebsiteController@UsersCount');
        
        });
        
        Route::get('{website_slug}/post_types/{post_type_slug}', function ($website_slug, $post_type_slug) {

            // $website = DB::table('websites')
            //     ->where('websites.slug', '=', $website_slug)
            //     ->first();

            // if($website == null){
            //     return response()->json('bad request');
            // }

            $post_type = DB::table('post_types')
                ->where('post_types.slug', '=', $post_type_slug)
                // ->where('post_types.website_id', '=', $website->id)
                ->first();
            
            return response()->json($post_type);
        });

        Route::prefix('{website_slug}/posts/{post_type_slug}')->group(function () {

            Route::get('/','PostsControllerV2@list');
        
            Route::post('insert','PostsControllerV2@insert');
        
            Route::post('update/{post_id}','PostsControllerV2@update')->where('post_id', '[0-9]+');
        
            Route::delete('delete/{post_id}','PostsControllerV2@delete')->where('post_id', '[0-9]+');
        
            Route::get('show/{post_id}','PostsControllerV2@show')->where('post_id', '[0-9]+');
        
        });



        Route::prefix('files')->group(function () {
            Route::get('allfiles','PostsControllerV2@fileAllList');
            Route::get('/{id}','PostsControllerV2@filePostList');
            Route::post('addfile','PostsControllerV2@addfilewithcreatepost');
            Route::post('addfilepanel','PostsControllerV2@addfilePanel');
            Route::post('editfilepanel/{id}','PostsControllerV2@editfilePanel');//beklemede
            Route::post('addfile/{id}','PostsControllerV2@addfile');
            Route::delete('delete/{id}','PostsControllerV2@deletefile');
        
        });

        Route::prefix('{website_slug}/siteseoredirect')->group(function () {  

            Route::get('/','PostsControllerV2@siteseoredirecturlList'); 
            Route::get('show/{id}','PostsControllerV2@siteseoredirecturlshow');
            Route::post('insert','PostsControllerV2@siteseoredirecturlassign');
            Route::put('edit/{id}','PostsControllerV2@siteseoredirecturledit');
            Route::delete('delete/{id}','PostsControllerV2@siteseoredirecturldelete');
        
        });

        Route::prefix('{website_slug}/siteseometa')->group(function () {  

            Route::get('/','PostsControllerV2@siteseoMetaList');
            Route::get('show/{id}','PostsControllerV2@siteseoMetaShow');
            Route::put('edit/{id}','PostsControllerV2@siteseoMetaEdit');
            Route::put('clean/{id}','PostsControllerV2@siteseoMetaClean');
        
        });


        Route::prefix('{website_slug}/siteseoTags')->group(function () {

            Route::get('/','PostsControllerV2@siteseoTagList');
            Route::get('show/{id}','PostsControllerV2@siteseoTagShow');
            Route::post('post','PostsControllerV2@siteseoTagInsert');
            Route::put('edit/{id}','PostsControllerV2@siteseoTagEdit');
            Route::delete('delete/{id}','PostsControllerV2@siteseoTagDelete');
        
        });

        Route::prefix('seopanelredirect')->group(function () {  

            Route::get('/','PostsControllerV2@seopanelredirecturlList');
            Route::get('show/{id}','PostsControllerV2@seopanelredirecturlshow');
            Route::post('insert','PostsControllerV2@seopanelredirecturlassign');
            Route::put('edit/{id}','PostsControllerV2@seopanelredirecturledit');
            Route::delete('delete/{id}','PostsControllerV2@seopanelredirecturldelete');
        
        });

        Route::prefix('seopanelmeta')->group(function () {

            Route::get('/','PostsControllerV2@seopanelMetaList');
            Route::get('show/{id}','PostsControllerV2@seopanelMetaShow');
            Route::put('edit/{id}','PostsControllerV2@seopanelMetaEdit');
            Route::put('clean/{id}','PostsControllerV2@seopanelMetaClean');
        
        });

        Route::prefix('seopanelkategori')->group(function () {

            Route::get('/','KategoriController@seopanelkategoriList');
            Route::get('show/{id}','KategoriController@seopanelkategorishow');
            Route::post('post','KategoriController@seopanelkategoriinsert');
            Route::put('edit/{id}','KategoriController@seopanelkategoriedit');
            Route::delete('delete/{id}','KategoriController@seopanelkategoriDelete');
        
        });

   
        Route::prefix('seopanelTags')->group(function () {

            Route::get('/','PostsControllerV2@seopanelTagList');
            Route::get('show/{id}','PostsControllerV2@seopanelTagShow');
            Route::post('post','PostsControllerV2@seopanelTagInsert');
            Route::put('edit/{id}','PostsControllerV2@seopanelTagEdit');
            Route::delete('delete/{id}','PostsControllerV2@seopanelTagDelete');
        
        });

        Route::prefix('mediaAllList')->group(function () {

            Route::get('/','PostsControllerV2@mediaImageAllList');
            Route::get('show/{id}','PostsControllerV2@mediaImagePanelshow');
            Route::put('edit/{id}','PostsControllerV2@mediaImagePanelupdate');
         
        });

        Route::get('allpostslist','PostsControllerV2@allpostslist');

        Route::get('{website_slug}/sitepostslist','PostsControllerV2@sitepostslist');

        Route::get('{website_slug}/postType','KategoriController@kategoriTypeList');

        Route::get('{website_slug}/mediaList','PostsControllerV2@mediaImageList');

        Route::get('mediaAllList','PostsControllerV2@mediaImageAllList');

        Route::post('{website_slug}/mediaInsert','PostsControllerV2@mediaImageInsert');

        Route::delete('mediaDelete/{id}','PostsControllerV2@mediaImageDelete');

        Route::get('{website_slug}/languageList','PostsControllerV2@languageList');

        Route::get('postTypeList','PostsControllerV2@postTypeList');

        Route::put('update/postTypeList/{id}','PostsControllerV2@postTypeUpdate');

        Route::get('{website_slug}/filelist','PostsControllerV2@fileList');
        
    });
    
});




Route::prefix('{website_slug}/posts/{post_type_slug}')->group(function () {

    Route::get('/','PostsControllerV2@list');
    Route::get('show/{slug}','PostsControllerV2@publicShow');

});

Route::prefix('{website_slug}/cozumler')->group(function () {
            
    Route::get('/','CozumlerController@index');

    Route::get('randomIndex','CozumlerController@randomIndex'); 

    Route::get('show/{slug}','CozumlerController@publicShow');

});

Route::prefix('{website_slug}/hizmetler')->group(function () {
            
    Route::get('/','HizmetlerController@index');

    Route::get('show/{slug}','HizmetlerController@show');

});

Route::prefix('{website_slug}/referanslar')->group(function () {
            
    Route::get('/','ReferanslarController@index');
    
    Route::get('randomIndex','ReferanslarController@randomIndex');

    Route::get('show/{slug}','ReferanslarController@show');

});

Route::prefix('{website_slug}/blog')->group(function () {

    Route::get('/','PostsController@index')->defaults('post_type_id', '2');

    Route::get('randomIndex','PostsController@randomIndex')->defaults('post_type_id', '2');

    Route::get('show/{slug}','PostsController@publicShow')->defaults('post_type_id', '2');
    
    Route::get('tag/{tag_name}','PostsController@tag')->defaults('post_type_id', '2');

    Route::get('related/{slug}','PostsController@related')->defaults('post_type_id', '2');

    Route::get('allTags','PostsController@allTags')->defaults('post_type_id', '2');

});



Route::prefix('{website_slug}/sayfalar')->group(function () {

    Route::get('/','PostsController@index')->defaults('post_type_id', '1');

    Route::get('show/{slug}','PostsController@publicShow')->defaults('post_type_id', '1');
    
});

Route::prefix('{website_slug}/destek')->group(function () {

    Route::get('show/{slug}','PostsController@publicShow')->defaults('post_type_id', '7');
    
});
 

Route::prefix('{website_slug}/teklif')->group(function () {

    Route::post('/','TeklifController@index');

    Route::post('post','TeklifController@store');
 
});

Route::prefix('{website_slug}/posts')->group(function () {
            
    Route::get('/','PostsController@index');

    Route::get('randomIndex','PostsController@randomIndex');

    Route::get('show/{slug}','PostsController@publicShow');

});

Route::prefix('{website_slug}/updates')->group(function () {

    Route::get('/','PostsController@index')->defaults('post_type_id', '6');
    Route::get('show/{slug}','PostsController@publicShow')->defaults('post_type_id', '6');

});

Route::prefix('{website_slug}/{post_slug}/categories')->group(function () {

    Route::get('/','PostsController@categoriesWithPosts')->defaults('post_type_id', '7');

    Route::get('{category_slug}','PostsController@categoryPosts');

});

Route::get('{website_slug}/arama/{slug}','PostsControllerV2@searchTerms');

Route::get('{website_slug}/{post_slug}/categorylist/{slug}','PostsController@categoryList');

Route::get('{website_slug}/kategori/show/{slug}','KategoriController@show');

Route::get('{website_slug}/kategori/{post_type}','KategoriController@categorylistPostType');

Route::get('{website_slug}/alltagposts/{slug}','PostsControllerV2@allTagPostList');

Route::get('{website_slug}/tag/{slug}','PostsControllerV2@tagShow');

Route::get('{website_slug}/alltagListName','PostsControllerV2@alltagListName');

Route::get('{website_slug}/tag/{post_type}/allPostTypeTags','PostsControllerV2@allPostTypeTags');

Route::get('{website_slug}/tag/{post_type}/{slug}','PostsControllerV2@tagPostList');

Route::get('{website_slug}/{post_type}/redirect/{slug}','PostsControllerV2@websitepostslist');

Route::get('files/{id}','PostsControllerV2@filePostList');

//Route::get('{website_slug}/trySitemap','PostsController@trySitemap');


