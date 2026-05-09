<?php

namespace Unusualify\Modularous\Console\Setup;

use Illuminate\Database\DatabaseManager;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularous\Console\BaseCommand;

class CreateDatabaseCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularous:create:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the database if it does not exist';

    /**
     * @var DatabaseManager
     */
    protected $db;

    public function __construct(DatabaseManager $db)
    {
        parent::__construct();

        $this->db = $db;
    }

    protected function getOptions(): array
    {
        return [
            ['connection', null, InputOption::VALUE_OPTIONAL, 'The database connection to use', null],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = $this->option('connection') ?? config('database.default');
        $config = config("database.connections.{$connection}");
        $databaseName = $config['database'];
        $driver = $config['driver'];

        if ($driver === 'mysql') {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 3306;
            $pdo = new \PDO(
                "mysql:host={$host};port={$port}",
                $config['username'],
                $config['password']
            );
            $charset = $config['charset'] ?? 'utf8mb4';
            $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
        } elseif ($driver === 'pgsql') {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 5432;
            $pdo = new \PDO(
                "pgsql:host={$host};port={$port}",
                $config['username'],
                $config['password']
            );
            $pdo->exec("SELECT 'CREATE DATABASE {$databaseName}' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '{$databaseName}')");
        } elseif ($driver === 'sqlite') {
            $path = str_starts_with($databaseName, '/') ? $databaseName : base_path($databaseName);
            if (! file_exists($path)) {
                $dir = dirname($path);
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                touch($path);
            }
        } elseif ($driver === 'sqlsrv') {
            $host = $config['host'] ?? 'localhost';
            $port = $config['port'] ?? 1433;
            $pdo = new \PDO(
                "sqlsrv:Server={$host},{$port}",
                $config['username'],
                $config['password']
            );
            $pdo->exec("IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = '{$databaseName}') CREATE DATABASE [{$databaseName}]");
        } else {
            $this->error("Driver [{$driver}] is not supported for database creation.");

            return 1;
        }

        $this->db->purge($connection);

        $this->info("Database [{$databaseName}] created successfully.");

        return 0;
    }
}
