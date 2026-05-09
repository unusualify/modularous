<?php

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularous\Entities\TemporaryFilepond;
use Unusualify\Modularous\Services\FilepondManager;
use Unusualify\Modularous\Tests\TestCase;

class FilepondManagerTest extends TestCase
{
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Manual migration for testing fileponds since TestCase doesn't run all migrations
        $schema = $this->app['db']->connection()->getSchemaBuilder();
        $temporariesTable = modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries');

        if (! $schema->hasTable($temporariesTable)) {
            $schema->create($temporariesTable, function ($table) {
                $table->increments('id');
                $table->string('file_name');
                $table->string('folder_name');
                $table->string('input_role');
                $table->timestamps();
            });
        }

        Storage::fake('local');
        $this->manager = new FilepondManager;
    }

    /** @test */
    public function it_can_create_temporary_filepond()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $request = Request::create('/upload', 'POST', [], [], ['avatar' => $file]);
        $request->setLaravelSession(app('session')->driver('array'));

        $response = $this->manager->createTemporaryFilepond($request);

        $this->assertEquals(200, $response->getStatusCode());
        $folderName = $response->getContent();

        $this->assertDatabaseHas(modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries'), [
            'folder_name' => $folderName,
            'file_name' => 'avatar.jpg',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('public/fileponds/tmp/' . $folderName . '/avatar.jpg'));
    }

    /** @test */
    public function it_can_delete_temporary_filepond()
    {
        $folderName = 'test-folder-delete';
        $tmp = TemporaryFilepond::create([
            'folder_name' => $folderName,
            'file_name' => 'test.jpg',
            'input_role' => 'avatar',
        ]);
        Storage::disk('local')->makeDirectory('public/fileponds/tmp/' . $folderName);
        Storage::disk('local')->put('public/fileponds/tmp/' . $folderName . '/test.jpg', 'content');

        // request()->getContent() reads from php://input, we can simulate this by passing the content in the Request::create
        $request = Request::create('/delete', 'POST', [], [], [], [], $folderName);

        // We need to bind this request to the container for request()->getContent() to work if it uses the facade/app
        $this->app->instance('request', $request);

        $this->manager->deleteTemporaryFilepond($request);

        $this->assertDatabaseMissing(modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries'), ['folder_name' => $folderName]);
        $this->assertFalse(Storage::disk('local')->exists('public/fileponds/tmp/' . $folderName));
    }
}
