
@if ($localized)
Route::get('/', function () {
    //return view('welcome');
    return redirect(route('locale_home', config("app.locale")));
})->name('welcome');
Route::group(['prefix' => CrudGenerator::setLocale(), 'middleware' => ['web','crudgenlocalization'] ], function () {
    Route::get('', function() {
        return redirect(route('home'));
    })->name('locale_home');
    Route::get('/home', 'HomeController@index')->name('home');
@endif    
    
    Route::group(['prefix' => CrudGenerator::transRouteModel("{modelo}s") . "/" , 'as' => "{modelo}."], function() {
        Route::get('', '{Modelo}Controller@index')->name('index');
        Route::get('/' . CrudGenerator::transRoute('create'), '{Modelo}Controller@create')->name('create');
        Route::post('/' . CrudGenerator::transRoute('store'), '{Modelo}Controller@store')->name('store');
        Route::get('/{ {modelo} }', '{Modelo}Controller@show')->name('show');
        Route::get('/{ {modelo} }/' . CrudGenerator::transRoute('edit'), '{Modelo}Controller@edit')->name('edit');
        Route::put('/{ {modelo} }/' . CrudGenerator::transRoute('update'), '{Modelo}Controller@update')->name('update');
        Route::delete('/{ {modelo} }/' . CrudGenerator::transRoute('destroy'), '{Modelo}Controller@destroy')->name('destroy');
    });
@if ($localized)
});
@endif