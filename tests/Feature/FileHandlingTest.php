<?php

namespace Sirgrimorum\CrudGenerator\Tests\Feature;

use Sirgrimorum\CrudGenerator\Tests\TestCase;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Sirgrimorum\CrudGenerator\CrudController;

#[CoversClass(CrudController::class)]
class FileHandlingTest extends TestCase
{
    public function test_save_file_from_request()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('test.txt', 10);
        $request = Request::create('/', 'POST', [], [], ['image' => $file]);

        $detalles = [
            'path' => 'uploads/images',
            'pre' => 'img_',
        ];

        $filename = CrudGenerator::saveFileFromRequest(null, $request, 'image', $detalles);

        $this->assertIsString($filename);
        $this->assertStringContainsString('img_', $filename);
        Storage::disk('local')->assertExists('uploads/images/' . $filename);
    }
}
