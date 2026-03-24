<?php

namespace Unusualify\Modularity\Tests\Support\Decomposers;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\UFinder;
use Unusualify\Modularity\Support\Decomposers\ModelRelationParser;
use Unusualify\Modularity\Tests\TestCase;

class ModelRelationParserTest extends TestCase
{
    protected $parser;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularity.laravel-relationship-map', [
            'belongsTo' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'ownerKey' => ['position' => 2, 'required' => false],
                'relation' => ['position' => 3, 'required' => false],
            ],
            'hasMany' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'localKey' => ['position' => 2, 'required' => false],
            ],
            'belongsToMany' => [
                'related' => ['position' => 0, 'required' => true],
                'table' => ['position' => 1, 'required' => false],
                'foreignPivotKey' => ['position' => 2, 'required' => false],
                'relatedPivotKey' => ['position' => 3, 'required' => false],
                'parentKey' => ['position' => 4, 'required' => false],
                'relatedKey' => ['position' => 5, 'required' => false],
                'relation' => ['position' => 6, 'required' => false],
            ],
        ]);

        UFinder::shouldReceive('getPossibleModels')->andReturnUsing(function ($str) {
            return ['App\\Models\\' . ucfirst($str)];
        });
        Modularity::shouldReceive('getModels')->andReturn([]);
    }

    /** @test */
    public function it_can_parse_simple_belongs_to_relation()
    {
        $parser = new ModelRelationParser('Post', 'belongsTo:User');
        $parsed = $parser->toArray();

        $this->assertCount(1, $parsed);
        $this->assertEquals('belongsTo', $parsed[0]['relationship_method']);
        $this->assertEquals('user', $parsed[0]['relationship_name']);
        // The parser adds the class suffix usually or assumes it
        // Depending on RelationshipMap trait implementation
    }

    /** @test */
    public function it_can_parse_multiple_relations()
    {
        $parser = new ModelRelationParser('Post', 'belongsTo:User|hasMany:Comment');
        $parsed = $parser->toArray();

        $this->assertCount(2, $parsed);
        $this->assertEquals('belongsTo', $parsed[0]['relationship_method']);
        $this->assertEquals('hasMany', $parsed[1]['relationship_method']);
    }

    /** @test */
    public function it_can_parse_belongs_to_many_with_pivot_fields()
    {
        $parser = new ModelRelationParser('Post', 'belongsToMany:Tag,active:boolean,position:integer');

        $this->assertTrue($parser->hasCreatablePivotModel());

        $pivots = $parser->getPivotModels();
        $this->assertCount(1, $pivots);
        $this->assertEquals('PostTag', $pivots[0]['class']);
        $this->assertContains('active', $pivots[0]['fillables']);
        $this->assertContains('position', $pivots[0]['fillables']);
        $this->assertEquals('string', $pivots[0]['casts']['active']); // Boolean casted to string in castFieldType
    }

    /** @test */
    public function it_generates_correct_pivot_model_name()
    {
        $parser = new ModelRelationParser('Post');
        $this->assertEquals('PostTag', $parser->getPivotModelName('tags'));
    }
}
