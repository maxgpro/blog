<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddMenu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->insert([
            [
                'title' => 'Panel',
                'parent' => 0,
                'sort_order' => 100,
                'path' => 'panel.index',
                'type' => 'admin'
            ],
            [
                'title' => 'Child',
                'parent' => 1,
                'sort_order' => 100,
                'path' => 'panel.index',
                'type' => 'admin'
            ],
        ]);
    }
}
