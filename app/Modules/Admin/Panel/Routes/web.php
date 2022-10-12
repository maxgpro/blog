<?php

Route::group(['prefix' => 'panel', 'middleware' => []], function () {
    Route::get('/', 'PanelController@index')->name('panel.index');
});
