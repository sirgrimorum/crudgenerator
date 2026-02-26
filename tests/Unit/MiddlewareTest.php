<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

#[CoversClass(CrudGenerator::class)]
class MiddlewareTest extends TestCase
{
    public function test_set_locale_from_url_segment()
    {
        // Mock a request with 'es' as the first segment
        $request = Request::create('/es/admin/stubmodels', 'GET');
        $this->app->instance('request', $request);
        
        $this->app['config']->set('sirgrimorum.crudgenerator.list_locales', ['en', 'es']);
        $this->app['config']->set('app.locale', 'en');

        $result = CrudGenerator::setLocale();

        $this->assertEquals('es', $result);
        $this->assertEquals('es', App::getLocale());
    }

    public function test_set_locale_defaults_to_app_locale_if_not_in_list()
    {
        // Mock a request with 'fr' as the first segment (not in list)
        $request = Request::create('/fr/admin/stubmodels', 'GET');
        $this->app->instance('request', $request);
        
        $this->app['config']->set('sirgrimorum.crudgenerator.list_locales', ['en', 'es']);
        $this->app['config']->set('app.locale', 'en');

        $result = CrudGenerator::setLocale();

        $this->assertEquals('en', $result);
        $this->assertEquals('en', App::getLocale());
    }
}
