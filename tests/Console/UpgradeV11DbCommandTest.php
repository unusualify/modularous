<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Console\Update\UpgradeV11DbCommand;
use Unusualify\Modularous\Tests\TestCase;

class UpgradeV11DbCommandTest extends TestCase
{
    private string $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = config('payable.table');

        Schema::dropIfExists($this->table);
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->json('parameters')->nullable();
        });
    }

    public function test_renames_legacy_modularity_key_in_payment_parameters(): void
    {
        $legacyId = $this->insertPayment(['modularity' => ['original_raw_amount' => 1000], 'other' => 1]);
        $currentId = $this->insertPayment(['modularous' => ['original_raw_amount' => 2000]]);
        $bothId = $this->insertPayment(['modularity' => ['a' => 1], 'modularous' => ['b' => 2]]);
        $emptyId = $this->insertPayment(null);

        $this->runFixPaymentParameters(dryRun: false);

        $this->assertSame(
            ['modularous' => ['original_raw_amount' => 1000], 'other' => 1],
            $this->parametersOf($legacyId)
        );
        $this->assertSame(['modularous' => ['original_raw_amount' => 2000]], $this->parametersOf($currentId));
        $this->assertSame(['modularity' => ['a' => 1], 'modularous' => ['b' => 2]], $this->parametersOf($bothId));
        $this->assertNull($this->parametersOf($emptyId));
    }

    public function test_dry_run_does_not_write(): void
    {
        $id = $this->insertPayment(['modularity' => ['original_raw_amount' => 1000]]);

        $this->runFixPaymentParameters(dryRun: true);

        $this->assertSame(['modularity' => ['original_raw_amount' => 1000]], $this->parametersOf($id));
    }

    private function runFixPaymentParameters(bool $dryRun): void
    {
        $command = $this->app->make(UpgradeV11DbCommand::class);
        $command->setLaravel($this->app);
        $command->setOutput(new \Illuminate\Console\OutputStyle(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput
        ));

        $method = new \ReflectionMethod($command, 'fixPaymentParameters');
        $method->invoke($command, $dryRun);
    }

    private function insertPayment(?array $parameters): int
    {
        return (int) DB::table($this->table)->insertGetId([
            'parameters' => $parameters === null ? null : json_encode($parameters),
        ]);
    }

    private function parametersOf(int $id): ?array
    {
        $raw = DB::table($this->table)->where('id', $id)->value('parameters');

        return $raw === null ? null : json_decode($raw, true);
    }
}
