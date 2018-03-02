<?php

Route::get(config("sirgrimorum.crudgenerator.admin_prefix"), function($localecode = null) {
        //return $localecode;
    return redirect(route('sirgrimorum_home'));
})->name('_sirgrimorum_home');

Route::group(['prefix' => Sirgrimorum\CrudGenerator\CrudGenerator::setLocale() . "/" . config("sirgrimorum.crudgenerator.admin_prefix"), 'middleware' => ['web','crudgenlocalization']], function () {
    Route::get('', function() {
        $callback = config('sirgrimorum.crudgenerator.permission');
        if (is_callable($callback)) {
            $resultado = (bool) $callback();
        } else {
            $resultado = (bool) $callback;
        }
        if (!$resultado) {
            return redirect(config("sirgrimorum.crudgenerator.login_path"))->with([
                        config("sirgrimorum.crudgenerator.error_messages_key") => trans('crudgenerator::admin.mensajes.permission'),
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_home")
                            ]
            );
        }
        return view('sirgrimorum::admin/templates/html');
    })->name('sirgrimorum_home');
    Route::group(['prefix' => "{modelo}s/", 'as' => "sirgrimorum_modelos::"], function() {
        Route::get('', '\Sirgrimorum\CrudGenerator\CrudController@index')->name('index');
        Route::get('/create', '\Sirgrimorum\CrudGenerator\CrudController@create')->name('create');
    });
    Route::group(['prefix' => "{modelo}/", 'as' => "sirgrimorum_modelo::"], function() {
        Route::post('/store', '\Sirgrimorum\CrudGenerator\CrudController@store')->name('store');
        Route::get('/{registro}', '\Sirgrimorum\CrudGenerator\CrudController@show')->name('show');
        Route::get('/{registro}/edit', '\Sirgrimorum\CrudGenerator\CrudController@edit')->name('edit');
        Route::put('/{registro}/update', '\Sirgrimorum\CrudGenerator\CrudController@update')->name('update');
        Route::delete('/{registro}/destroy', '\Sirgrimorum\CrudGenerator\CrudController@destroy')->name('destroy');
        Route::get('/file/{campo}', '\Sirgrimorum\CrudGenerator\CrudController@modelfile')->name('modelfile');
    });
    Route::get('/file', '\Sirgrimorum\CrudGenerator\CrudController@file')->name('sirgrimorum_file');
});
