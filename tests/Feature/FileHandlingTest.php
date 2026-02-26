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
        $file = UploadedFile::fake()->create('test.txt', 10);
        $request = Request::create('/', 'POST', [], [], ['image' => $file]);
        
        $detalles = [
            'path' => 'uploads/images',
            'pre' => 'img_',
        ];

        // We need to ensure the destination directory exists in the test app's public path
        $destinationPath = public_path($detalles['path']);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $filename = CrudGenerator::saveFileFromRequest($request, 'image', $detalles);

        $this->assertIsString($filename);
        $this->assertStringContainsString('img_', $filename);
        $this->assertFileExists(public_path($detalles['path'] . '/' . $filename));
        
        // Clean up
        unlink(public_path($detalles['path'] . '/' . $filename));
    }
}
