<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\RelationshipArguments;

class RelationshipArgumentsTest extends TestCase
{
    protected function createTester(): object
    {
        return new class
        {
            use RelationshipArguments;
        };
    }

    public function test_get_relationship_argument_related_for_belongs_to()
    {
        $tester = $this->createTester();
        $this->assertEquals('User', $tester->getRelationshipArgumentRelated('user', 'belongsTo', []));
    }

    public function test_get_relationship_argument_related_for_has_many()
    {
        $tester = $this->createTester();
        $this->assertEquals('Comment', $tester->getRelationshipArgumentRelated('comment', 'hasMany', []));
    }

    public function test_get_relationship_argument_related_for_has_one()
    {
        $tester = $this->createTester();
        $this->assertEquals('Profile', $tester->getRelationshipArgumentRelated('profile', 'hasOne', []));
    }

    public function test_get_relationship_argument_related_for_belongs_to_many()
    {
        $tester = $this->createTester();
        $this->assertEquals('Tag', $tester->getRelationshipArgumentRelated('tag', 'belongsToMany', []));
    }

    public function test_get_relationship_argument_foreign_key_for_belongs_to()
    {
        $tester = $this->createTester();
        $this->assertEquals('user_id', $tester->getRelationshipArgumentForeignKey('user', 'belongsTo', []));
    }

    public function test_get_relationship_argument_foreign_key_for_belongs_to_with_explicit_arg()
    {
        $tester = $this->createTester();
        $this->assertEquals('author_id', $tester->getRelationshipArgumentForeignKey('user', 'belongsTo', ['author_id']));
    }

    public function test_get_relationship_argument_owner_key_for_belongs_to()
    {
        $tester = $this->createTester();
        $this->assertEquals('id', $tester->getRelationshipArgumentOwnerKey('user', 'belongsTo', []));
    }

    public function test_get_relationship_argument_owner_key_for_belongs_to_with_explicit_arg()
    {
        $tester = $this->createTester();
        $this->assertEquals('uuid', $tester->getRelationshipArgumentOwnerKey('user', 'belongsTo', ['user_id', 'uuid']));
    }

    public function test_get_relationship_argument_owner_key_returns_empty_for_has_many()
    {
        $tester = $this->createTester();
        $this->assertEquals('', $tester->getRelationshipArgumentOwnerKey('posts', 'hasMany', []));
    }

    public function test_get_relationship_argument_through_for_has_many_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('Country', $tester->getRelationshipArgumentThrough('posts', 'hasManyThrough', ['country']));
    }

    public function test_get_relationship_argument_through_for_has_one_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('Supplier', $tester->getRelationshipArgumentThrough('history', 'hasOneThrough', ['supplier']));
    }

    public function test_get_relationship_argument_first_key_for_has_one_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('id', $tester->getRelationshipArgumentFirstKey('history', 'hasOneThrough', [], 'User'));
    }

    public function test_get_relationship_argument_first_key_for_has_many_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('user_id', $tester->getRelationshipArgumentFirstKey('posts', 'hasManyThrough', [], 'User'));
    }

    public function test_get_relationship_argument_second_key_for_has_one_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('id', $tester->getRelationshipArgumentSecondKey('history', 'hasOneThrough', []));
    }

    public function test_get_relationship_argument_second_key_for_has_many_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('country_id', $tester->getRelationshipArgumentSecondKey('posts', 'hasManyThrough', ['country']));
    }

    public function test_get_relationship_argument_local_key_for_has_many_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('id', $tester->getRelationshipArgumentLocalKey('posts', 'hasManyThrough', []));
    }

    public function test_get_relationship_argument_second_local_key_for_has_many_through()
    {
        $tester = $this->createTester();
        $this->assertEquals('id', $tester->getRelationshipArgumentSecondLocalKey('posts', 'hasManyThrough', [], 'User'));
    }
}
