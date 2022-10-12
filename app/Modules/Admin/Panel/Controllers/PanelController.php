<?php

namespace App\Modules\Admin\Panel\Controllers;

use App\Modules\Admin\Panel\Classes\Base;

class PanelController extends Base
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->title = __("admin.dashboard_title_page");
        $this->content = view('Admin::Panel.Views.index')->with([
            'title' => $this->title
        ]);

        return $this->renderOutput();
    }

}
