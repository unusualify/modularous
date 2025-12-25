<?php

namespace Unusualify\Modularity\Tests\Repositories\Logic;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Mockery;
use Unusualify\Modularity\Tests\Repositories\TestModel;
use Unusualify\Modularity\Tests\Repositories\TestRepository;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class CollationSelectorTest extends RepositoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock repository with CollationSelector trait
     */
    protected function createMockRepository(array $columnTypes = []): Mockery\MockInterface
    {
        $model = Mockery::mock(TestModel::class)->makePartial();
        $model->shouldReceive('getColumnTypes')->andReturn($columnTypes);

        $mock = Mockery::mock(TestRepository::class, [$model])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('getModel')->andReturn($model);

        return $mock;
    }

    /**
     * Create a mock query builder with MySQL connection
     */
    protected function createMySqlQueryBuilder(string $collation = 'utf8mb4_unicode_ci'): Mockery\MockInterface
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getDriverName')->andReturn('mysql');
        $connection->shouldReceive('getConfig')->andReturn(['collation' => $collation]);

        $grammar = new MySqlGrammar();

        $queryBuilder = Mockery::mock(EloquentBuilder::class)->makePartial();
        $queryBuilder->shouldReceive('getConnection')->andReturn($connection);
        $queryBuilder->shouldReceive('getGrammar')->andReturn($grammar);

        return $queryBuilder;
    }

    /**
     * Create a mock query builder with PostgreSQL connection
     */
    protected function createPostgresQueryBuilder(): Mockery\MockInterface
    {
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getDriverName')->andReturn('pgsql');

        $queryBuilder = Mockery::mock(EloquentBuilder::class)->makePartial();
        $queryBuilder->shouldReceive('getConnection')->andReturn($connection);

        return $queryBuilder;
    }

    // =========================================================================
    // isCollationQuery() Tests
    // =========================================================================

    public function test_is_collation_query_returns_true_for_mysql(): void
    {
        $mock = $this->createMockRepository();
        $query = $this->createMySqlQueryBuilder();

        $result = $mock->isCollationQuery($query);

        $this->assertTrue($result);
    }

    public function test_is_collation_query_returns_false_for_postgres(): void
    {
        $mock = $this->createMockRepository();
        $query = $this->createPostgresQueryBuilder();

        $result = $mock->isCollationQuery($query);

        $this->assertFalse($result);
    }

    // =========================================================================
    // shouldUseSearchCollation() Tests
    // =========================================================================
    public function test_should_use_search_collation_returns_true_when_modularity_config_enabled_and_mysql(): void
    {
        config(['database.default' => 'mysql']);
        config(['modularity.use_collation_for_search' => true]);

        $mock = $this->createMockRepository();

        $query = $this->createMySqlQueryBuilder();        $shouldUseCollationForSearch = true;

        $mock->shouldReceive('isCollationQuery')->with($query)->andReturn(true);

        $result = $mock->shouldUseSearchCollation($query);

        $this->assertTrue($result);
        $this->assertEquals($shouldUseCollationForSearch, true);
    }

    public function test_should_use_search_collation_returns_true_when_repository_property_enabled_and_mysql(): void
    {
        $mock = $this->createMockRepository();
        $mock->setShouldUseSearchCollation(true);

        $query = $this->createMySqlQueryBuilder();

        $result = $mock->shouldUseSearchCollation($query);

        $this->assertTrue($result);
    }

    public function test_should_use_search_collation_returns_false_when_not_mysql(): void
    {
        $mock = $this->createMockRepository();
        $mock->setShouldUseSearchCollation(true);

        $query = $this->createPostgresQueryBuilder();

        $result = $mock->shouldUseSearchCollation($query);

        $this->assertFalse($result);
    }

    public function test_should_use_search_collation_returns_false_when_disabled(): void
    {
        $mock = $this->createMockRepository();
        $mock->setShouldUseSearchCollation(false);

        $query = $this->createMySqlQueryBuilder();

        $this->partialMock(\Unusualify\Modularity\Modularity::class, function ($mock) {
            $mock->shouldReceive('shouldUseCollationForSearch')->andReturn(false);
        });

        $result = $mock->shouldUseSearchCollation($query);

        $this->assertFalse($result);
    }

    // =========================================================================
    // addSearchCollationToQuery() - JSON Fields Tests
    // =========================================================================

    public function test_add_search_collation_to_query_handles_json_field_with_arrow_syntax(): void
    {
        $mock = $this->createMockRepository();
        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('whereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, 'CAST(')
                    && str_contains($sql, 'AS CHAR)')
                    && str_contains($sql, 'COLLATE utf8mb4_unicode_ci')
                    && str_contains($sql, 'LIKE ?')
                    && $bindings === ['%test%'];
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'content->title', 'test');

        $this->assertSame($query, $result);
    }

    public function test_add_search_collation_to_query_handles_nested_json_field(): void
    {
        $mock = $this->createMockRepository();
        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('whereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, 'CAST(')
                    && str_contains($sql, 'AS CHAR)')
                    && str_contains($sql, 'COLLATE utf8mb4_unicode_ci')
                    && $bindings === ['%search%'];
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'data->nested->field', 'search');

        $this->assertSame($query, $result);
    }

    // =========================================================================
    // addSearchCollationToQuery() - Text Column Tests
    // =========================================================================

    public function test_add_search_collation_to_query_applies_collation_for_text_column(): void
    {
        $mock = $this->createMockRepository(['description' => 'text']);
        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('orWhereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, '`description`')
                    && str_contains($sql, 'LIKE ?')
                    && str_contains($sql, 'COLLATE utf8mb4_unicode_ci')
                    && $bindings === ['%value%'];
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'description', 'value');

        $this->assertSame($query, $result);
    }

    public function test_add_search_collation_to_query_applies_collation_for_longtext_column(): void
    {
        $mock = $this->createMockRepository(['content' => 'longtext']);
        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('orWhereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, 'LIKE ?')
                    && str_contains($sql, 'COLLATE utf8mb4_unicode_ci')
                    && $bindings === ['%content%'];
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'content', 'content');

        $this->assertSame($query, $result);
    }

    // =========================================================================
    // addSearchCollationToQuery() - Non-Collation Column Tests
    // =========================================================================

    public function test_add_search_collation_to_query_applies_collation_for_varchar_column(): void
    {
        $mock = $this->createMockRepository(['name' => 'varchar']);

        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('orWhereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, '`name`')
                    && str_contains($sql, 'LIKE ?')
                    && str_contains($sql, 'COLLATE utf8mb4_unicode_ci')
                    && $bindings === ['%test%'];
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'name', 'test');

        $this->assertSame($query, $result);
    }

    public function test_add_search_collation_to_query_uses_simple_where_for_integer(): void
    {
        $mock = $this->createMockRepository(['id' => 'integer']);
        $mock->shouldReceive('getLikeOperator')->andReturn('LIKE');

        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('orWhere')
            ->once()
            ->with('id', 'LIKE', '%123%')
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'id', '123');

        $this->assertSame($query, $result);
    }

    public function test_add_search_collation_to_query_uses_simple_where_for_unknown_column(): void
    {
        $mock = $this->createMockRepository([]);
        $mock->shouldReceive('getLikeOperator')->andReturn('LIKE');

        $query = $this->createMySqlQueryBuilder();

        $query->shouldReceive('orWhere')
            ->once()
            ->with('unknown_field', 'LIKE', '%search%')
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'unknown_field', 'search');

        $this->assertSame($query, $result);
    }

    // =========================================================================
    // addSearchCollationToQuery() - Custom Collation Tests
    // =========================================================================

    public function test_add_search_collation_to_query_uses_custom_collation_from_config(): void
    {
        $mock = $this->createMockRepository(['description' => 'text']);
        $query = $this->createMySqlQueryBuilder('utf8mb4_general_ci');

        $query->shouldReceive('orWhereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, 'COLLATE utf8mb4_general_ci');
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'description', 'test');

        $this->assertSame($query, $result);
    }

    public function test_add_search_collation_to_query_uses_default_collation_when_not_configured(): void
    {
        $mock = $this->createMockRepository(['description' => 'text']);

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('getDriverName')->andReturn('mysql');
        $connection->shouldReceive('getConfig')->andReturn([]); // No collation in config

        $grammar = new MySqlGrammar();

        $query = Mockery::mock(EloquentBuilder::class)->makePartial();
        $query->shouldReceive('getConnection')->andReturn($connection);
        $query->shouldReceive('getGrammar')->andReturn($grammar);

        $query->shouldReceive('orWhereRaw')
            ->once()
            ->withArgs(function ($sql, $bindings) {
                return str_contains($sql, 'COLLATE utf8mb4_unicode_ci'); // Default collation
            })
            ->andReturnSelf();

        $result = $mock->addSearchCollationToQuery($query, 'description', 'test');

        $this->assertSame($query, $result);
    }

    // =========================================================================
    // collationSelectorColumns Property Tests
    // =========================================================================

    public function test_collation_selector_columns_includes_all_character_types(): void
    {
        $mock = $this->createMockRepository();

        $columns = $mock->getCollationSelectorColumns();

        $expectedTypes = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set'];

        foreach ($expectedTypes as $type) {
            $this->assertContains($type, $columns, "Column type '$type' should be in collationSelectorColumns");
        }
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================
}
