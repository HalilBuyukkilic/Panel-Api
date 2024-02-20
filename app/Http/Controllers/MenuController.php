<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\WebsiteMenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Query\Builder;
use App\Http\Controllers\Abstracts\HttpResult;

class MenuController extends Controller
{

    public function index(Request $request)
    {
        $menus = WebsiteMenu::with(['website'])->get();
        return  HttpResult::success(true)->data($menus)->toJsonResponse();
    }

    public function getWebsiteMenus(Request $request, $websiteId)
    {
        $menus = WebsiteMenu::with(['website'])->where('website_id', '=', $websiteId)->get();
        return  HttpResult::success(true)->data($menus)->toJsonResponse();
    }

    public function add(Request $request)
    {
        $menu = WebsiteMenu::create($request->only([
            'website_id',
            'parent_id',
            'title',
            'description',
            'icon',
            'link',
            'menu_order',
        ]));

        return HttpResult::success(true)->data($menu)->toJsonResponse();
    }

    public function get(Request $request, $id)
    {
        $menu = WebsiteMenu::find($id);
        return HttpResult::success(true)->data($menu)->toJsonResponse();
    }

    public function update(Request $request, $id)
    {
        $menu = WebsiteMenu::where('id', $id)->update($request->only([
            'website_id',
            'parent_id',
            'title',
            'description',
            'icon',
            'link',
            'menu_order',
        ]));

        return HttpResult::success(true)->data($menu)->toJsonResponse();
    }

    public function delete(Request $request, $id)
    {
        $menu = WebsiteMenu::destroy($id);
        return HttpResult::success(true)->toJsonResponse();
    }
}
