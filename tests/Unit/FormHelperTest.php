<?php

namespace Sirgrimorum\CrudGenerator\Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\Helpers\FormHelper;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FormHelper::class)]
class FormHelperTest extends TestCase
{
    public function test_escaped_echo_prints_markup_like_laravelcollective()
    {
        $html = Blade::render('{{ Form::text("name", "a & b") }}{{ Form::select("tipo", ["1" => "Uno"], "1") }}');

        $this->assertStringContainsString('<input type="text" name="name" value="a &amp; b"', $html);
        $this->assertStringContainsString('<option value="1" selected="selected">Uno</option>', $html);
    }

    public function test_label_still_escapes_its_text()
    {
        $this->assertStringContainsString('>&lt;b&gt;</label>', (string) FormHelper::label('name', '<b>'));
    }
}
