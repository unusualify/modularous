<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\RelationshipMap;
use Unusualify\Modularity\Traits\RelationshipArguments;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Facades\UFinder;

class RelationshipTraitsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('modularity.laravel-relationship-map', [
            'belongsTo' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'ownerKey' => ['position' => 2, 'required' => false],
            ],
            'hasMany' => [
                'related' => ['position' => 0, 'required' => true],
                'foreignKey' => ['position' => 1, 'required' => false],
                'localKey' => ['position' => 2, 'required' => false],
            ]
        ]);

        UFinder::shouldReceive('getPossibleModels')->andReturnUsing(function($str) {
            return ["App\\Models\\" . ucfirst($str)];
        });
    }

    /** @test */
    public function it_can_generate_relationship_schema()
    {
        $tester = new class { 
            use RelationshipMap; 
            public function boot($model) { 
                $this->model = $model; 
                $this->relationshipParametersMap = config('modularity.laravel-relationship-map');
            }
            public function run($name, $rel, $args = []) { return $this->createRelationshipSchema($name, $rel, $args); }
        };
        $tester->boot('Post');

        // belongsTo:User -> user:belongsTo:App\Models\User
        // Actually createRelationshipSchema returns the string representation used in decomposers
        $schema = $tester->run('User', 'belongsTo');
        $this->assertEquals('belongsTo:User:user_id:id', $schema);
    }

    /** @test */
    public function it_can_parse_relationship_schema()
    {
        $tester = new class { 
            use RelationshipMap; 
            public function boot($model) { 
                $this->model = $model; 
                $this->relationshipParametersMap = config('modularity.laravel-relationship-map');
            }
        };
        $tester->boot('Post');

        $parsed = $tester->parseRelationshipSchema('belongsTo:User');
        
        $this->assertEquals('belongsTo', $parsed['relationship_method']);
        $this->assertEquals('user', $parsed['relationship_name']);
        $this->assertContains('\\App\\Models\\User::class', $parsed['arguments']);
    }

    /** @test */
    public function it_can_generate_relationship_arguments()
    {
        $tester = new class { use RelationshipArguments; };
        
        $arg = $tester->getRelationshipArgumentForeignKey('User', 'belongsTo', []);
        $this->assertEquals('user_id', $arg);
        
        $arg = $tester->getRelationshipArgumentOwnerKey('User', 'belongsTo', ['User', 'id']);
        $this->assertEquals('id', $arg);
    }
}
