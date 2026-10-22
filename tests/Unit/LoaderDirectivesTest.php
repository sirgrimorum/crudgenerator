<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\CoversClass;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use Sirgrimorum\CrudGenerator\CrudGeneratorServiceProvider;
use Sirgrimorum\CrudGenerator\Tests\TestCase;

#[CoversClass(CrudGenerator::class)]
#[CoversClass(CrudGeneratorServiceProvider::class)]
class LoaderDirectivesTest extends TestCase
{
    public function test_loader_directives_define_the_js_functions()
    {
        $this->assertStringContainsString('function scriptLoader(', Blade::compileString('@addScriptsLoader()'));
        $this->assertStringContainsString('function linkTagLoader(', Blade::compileString('@addLinkTagsLoader()'));
    }

    public function test_load_directives_call_the_js_functions()
    {
        $this->assertStringContainsString("scriptLoader('/js/a.js',true,", Blade::compileString("@loadScript('/js/a.js', true)"));
        $this->assertStringContainsString("linkTagLoader('/css/a.css','stylesheet','text/css')", Blade::compileString("@loadLinkTag('/css/a.css')"));
    }

    public function test_loader_html_helpers_are_not_empty()
    {
        $this->assertSame("<script>scriptLoader('/js/a.js',false,\"\",true);</script>", CrudGenerator::addScriptLoaderHtml('/js/a.js'));
        $this->assertSame("<script>linkTagLoader('/css/a.css','stylesheet','text/css');</script>", CrudGenerator::addLinkTagLoaderHtml('/css/a.css'));
    }
}
