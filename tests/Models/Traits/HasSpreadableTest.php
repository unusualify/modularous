<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasSpreadableTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_spreadable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->model = new TestSpreadableModel(['name' => 'Test Spreadable Model']);

        $this->model->spread_payload = [
            'type' => 1,
            'transfer_details' => [
                'account_holder' => 'B2Press B.V.',
                'iban' => 'NL66 ABNA 0127 4897 70',
                'swift_code' => 'ABNANL2AXXX',
                'description' => '[Company name] - Payment for PR Distribution',
                'address' => 'ABN AMRO B.V. Gustav Mahlerlaan 10 - 1082 PP Amsterdam - The Netherlands',
            ],
        ];

        $this->model->save();
    }

    public function test_it_can_create_a_spreadable_model()
    {
        $this->assertDatabaseHas('test_spreadable_models', ['name' => 'Test Spreadable Model']);
    }

    public function test_it_get_without_spreadable_saving_key()
    {
        $this->model->spreadable()->delete();
        $this->model->refresh();

        $this->model = new TestSpreadableModel(['name' => 'New Test Spreadable Model']);
        $this->model->save();

        $this->assertEquals('New Test Spreadable Model', $this->model->name);

    }

    public function test_spreadable_is_not_created()
    {
        $this->assertNotNull($this->model->spreadable);
        $originalSpreadableId = $this->model->spreadable->id;

        // Delete the spreadable relationship
        $this->model->spreadable()->delete();
        $this->model->refresh();

        // Verify that spreadable is null after deletion
        $this->assertNull($this->model->spreadable);

        // Update the model with a new spread_payload
        $newPayload = [
            'type' => 2,
            'transfer_details' => [
                'account_holder' => 'New Account Holder',
                'iban' => 'NL91 ABNA 0417 1643 00',
                'swift_code' => 'ABNANL2AXXX',
                'description' => 'New Payment Description',
                'address' => 'New Address',
            ],
        ];

        $this->model->update([
            'name' => 'Updated Name After Spreadable Deletion',
            'spread_payload' => $newPayload,
        ]);

        $this->model->refresh();

        $this->assertNotNull($this->model->spreadable);
        $this->assertNotEquals($originalSpreadableId, $this->model->spreadable->id);
        $this->assertEquals('Updated Name After Spreadable Deletion', $this->model->name);
        $this->assertEquals($newPayload, $this->model->spreadable->content);
    }

    public function test_it_can_update_a_spreadable_model()
    {
        // dd($this->model->spreadable->content);
        $this->model->update([
            'name' => 'Updated Test Spreadable Model',
            'spread_payload' => [
                'type' => 1,
                'transfer_details' => [
                    'account_holder' => 'Updated Account Holder',
                    'iban' => 'NL66 ABNA 0127 4897 70',
                    'swift_code' => 'ABNANL2AXXX',
                    'description' => '[Company name] - Payment for PR Distribution',
                    'address' => 'ABN AMRO B.V. Gustav Mahlerlaan 10 - 1082 PP Amsterdam - The Netherlands',
                ],
            ],
        ]);

        $this->model->refresh();

        $this->assertEquals('Updated Test Spreadable Model', $this->model->name);
        $this->assertEquals([
            'type' => 1,
            'transfer_details' => [
                'account_holder' => 'Updated Account Holder',
                'iban' => 'NL66 ABNA 0127 4897 70',
                'swift_code' => 'ABNANL2AXXX',
                'description' => '[Company name] - Payment for PR Distribution',
                'address' => 'ABN AMRO B.V. Gustav Mahlerlaan 10 - 1082 PP Amsterdam - The Netherlands',
            ],
        ], $this->model->spreadable->content);
    }

    public function test_it_get_spreadable_keys()
    {
        $this->model->update([
            'name' => 'Updated Test Spreadable Model',
            'spread_payload' => [
                'type' => 3,
                'transfer_details' => [
                    'account_holder' => 'Newly Updated Account Holder',
                    'iban' => 'NL66 ABNA 0127 4897 70',
                    'swift_code' => 'ABNANL2AXXX',
                    'description' => '[Company name] - Payment for PR Distribution',
                    'address' => 'ABN AMRO B.V. Gustav Mahlerlaan 10 - 1082 PP Amsterdam - The Netherlands',
                ],
            ],
        ]);

        $this->model->refresh();
        $model = TestSpreadableModel::find(1);

        $spreadableKeys = $model->getSpreadableKeys();
        $this->assertEquals(['type', 'transfer_details'], $spreadableKeys);
    }

    public function test_it_spreadable()
    {
        $content = [
            'type' => 2,
            'transfer_details' => [
                'account_holder' => 'B2Press B.V.22',
                'iban' => 'NL66 ABNA 0127 4897 7022',
                'swift_code' => 'ABNANL2AXXX',
                'description' => '[Company name] - Payment for PR Distribution',
                'address' => 'ABN AMRO B.V. Gustav Mahlerlaan 10 - 1082 PP Amsterdam - The Netherlands',
            ],
        ];

        $model = new TestSpreadableModel2([
            'name' => 'Test Spreadable Model 2',
            'spread_payload' => $content,
        ]);

        $model->save();

        $model->spreadable();

        $this->assertEquals('Test Spreadable Model 2', $model->name);
        $this->assertEquals($content, $model->spreadable->content);

    }
}

class TestSpreadableModel extends Model
{
    use HasSpreadable;

    protected $table = 'test_spreadable_models';

    protected $fillable = ['name'];
}

class TestSpreadableModel2 extends Model
{
    use HasSpreadable;

    protected $table = 'test_spreadable_models';

    protected $fillable = ['name'];

    protected static $spreadableClass = TestSpreadableModel::class;
}
