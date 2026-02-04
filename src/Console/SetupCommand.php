<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;

class SetupCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularity:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup system environments';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files, DatabaseManager $db)
    {
        parent::__construct();

        $this->files = $files;
        $this->db = $db;
    }

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        // check the database connection before installing
        try {
            $this->db->connection()->getPdo();
        } catch (\Exception $e) {

            // create database if not exists but via getting the consent of user
            $this->info('Database does not exist, do you want to create it? (y/n)');
            $answer = $this->choice('Do you want to create the database?', ['y', 'n']);

            if ($answer == 'y') {
                $this->createDatabase();
                $this->info('Database created successfully');
                $createdDatabase = true;
            } else {
                $this->error('Database creation cancelled');

                return 0;
            }
        }

        $this->call('migrate');

        $this->call('modularity:migrate', [
            'module' => 'SystemUtility',
        ]);

        $this->call('modularity:migrate', [
            'module' => 'SystemPayment',
        ]);

        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => \Unusualify\Modularity\Database\Seeders\DefaultDatabaseSeeder::class,
        ]);

        $this->publishConfig();

        $this->publishAssets();

        $this->createAdmin();

        $this->info('All good!');

        return 0;
    }

    /**
     * Creates the database by connecting without a database name first.
     */
    private function createDatabase(): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $databaseName = $config['database'];
        $driver = $config['driver'];

        // Connect without specifying database
        $config['database'] = null;

        if ($driver === 'mysql') {
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']}",
                $config['username'],
                $config['password']
            );
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$config['charset']} COLLATE {$config['collation']}");
        } elseif ($driver === 'pgsql') {
            $pdo = new \PDO(
                "pgsql:host={$config['host']};port={$config['port']}",
                $config['username'],
                $config['password']
            );
            $pdo->exec("SELECT 'CREATE DATABASE {$databaseName}' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '{$databaseName}')");
        } elseif ($driver === 'sqlite') {
            // For SQLite, just touching the file creates the database
            if (! file_exists($databaseName)) {
                touch($databaseName);
            }
        } elseif ($driver === 'sqlsrv') {
            $pdo = new \PDO(
                "sqlsrv:Server={$config['host']},{$config['port']}",
                $config['username'],
                $config['password']
            );
            $pdo->exec("IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = '{$databaseName}') CREATE DATABASE [{$databaseName}]");
        }

        // Purge and reconnect with the new database
        $this->db->purge($connection);
    }

    /**
     * Publishes the package configuration files.
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\Providers\ModularityProvider',
            '--tag' => 'config',
        ]);
    }

    /**
     * Publishes the package frontend assets.
     *
     * @return void
     */
    private function publishAssets()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularity\Providers\ModularityProvider',
            '--tag' => 'assets',
            '--force' => true,
        ]);
    }

    /**
     * Calls the command responsible for creation of the default superadmin user.
     *
     * @return void
     */
    private function createAdmin()
    {
        if (! $this->option('no-interaction')) {
            $this->call('modularity:create:superadmin');
        }
    }
}
