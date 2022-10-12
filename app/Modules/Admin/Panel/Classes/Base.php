<?php


namespace App\Modules\Admin\Panel\Classes;


use App\Http\Controllers\Controller;
use App\Http\Middleware\TrimStrings;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Modules\Admin\Menu\Models\Menu as MenuModel;
use Menu;

class Base extends Controller
{
    protected $template;
    protected $user;
    protected $vars;

    protected $title;
    protected $content;
    protected $sidebar;

    protected $locale;

    protected $service;

    public function __construct()
    {
        $this->template = "Admin::Panel.Views.panel";

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->locale = App::getLocale();
            return $next($request);
        });
    }

    protected function renderOutput() {
        $this->vars = Arr::add($this->vars, 'content', $this->content);
        $menu = $this->getMenu();
        $this->sidebar = view('Admin::Layouts.parts.sidebar')->with([
            'menu' => $menu,
            'user' => $this->user
        ])->render();
        $this->vars = Arr::add($this->vars, 'sidebar', $this->sidebar);

        return view($this->template)->with($this->vars);
    }

    private function getMenu() {
        return Menu::make('menuRenderer', function ($m) {
            foreach (MenuModel::menuByType(MenuModel::MENU_TYPE_ADMIN)->get() as $item) {
                $path = $item->path;
                if($path && $this->checkRoute($path)) {
                    $path = route($path);
                }

                if($item->parent == 0) {
                    $m->add($item->title, $path);
                }
                else {
                    if($m->find($item->parent)) {
                        $m->find($item->parent);
                    }
                }

            }
        });
    }

    private function checkRoute($path)
    {
        $routes = \Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            if ($route->getName() == $path) {
                return true;
            }
        }
        return false;
    }

//    private function getPermissions(mixed $item)
//    {
//        return $item->perms->map(function($item) {
//            return $item->alias;
//        })->toArray();
//    }
}
