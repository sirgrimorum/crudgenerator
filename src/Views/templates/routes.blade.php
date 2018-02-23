
@if ($localized)
Route::group(['prefix' => "{localecode}", 'middleware' => 'web'], function () {
    Route::get('', function($localecode) {
        App::setLocale($localecode);
        return redirect(route('home'));
    })->name('locale_home');
@endif    
    Route::group(['prefix' => "{modelo}s/", 'as' => "{modelo}."], function() {
        Route::get('', '{Modelo}Controller@index')->name('index');
        Route::get('/create', '{Modelo}Controller@create')->name('create');
        Route::post('/store', '{Modelo}Controller@store')->name('store');
        Route::get('/{ {modelo} }', '{Modelo}Controller@show')->name('show');
        Route::get('/{ {modelo} }/edit', '{Modelo}Controller@edit')->name('edit');
        Route::put('/{ {modelo} }/update', '{Modelo}Controller@update')->name('update');
        Route::delete('/{ {modelo} }/destroy', '{Modelo}Controller@destroy')->name('destroy');
    });
@if ($localized)
});
@endif