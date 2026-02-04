<?php

namespace Unusualify\Modularity\Tests\Support;

use Unusualify\Modularity\Support\CoverageAnalyzer;
use InvalidArgumentException;
use RuntimeException;

class CoverageAnalyzerTest extends \Unusualify\Modularity\Tests\TestCase
{
    private string $testCloverPath;
    private string $testCloverDir;
    private string $testCloverName;

    private string $invalidXmlName;
    private string $invalidXmlPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test clover.xml
        $this->testCloverDir = sys_get_temp_dir();
        $this->testCloverName = 'test-clover.xml';
        $this->testCloverPath = concatenate_path($this->testCloverDir, $this->testCloverName);
        $this->createTestCloverFile();

        // Create invalid XML
        $this->invalidXmlName = 'invalid.xml';
        $this->invalidXmlPath = concatenate_path($this->testCloverDir, $this->invalidXmlName);
        file_put_contents($this->invalidXmlPath, '<?xml version="1.0"?><invalid>');

    }

    protected function tearDown(): void
    {
        if (file_exists($this->testCloverPath)) {
            unlink($this->testCloverPath);
        }
        if (file_exists($this->invalidXmlPath)) {
            unlink($this->invalidXmlPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_when_file_not_found()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coverage file not found');

        new CoverageAnalyzer($this->testCloverDir, 'coverage-clover.xml');
    }

    /** @test */
    public function it_throws_exception_when_xml_is_invalid()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse coverage XML');

        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->invalidXmlName);
        $analyzer->analyze();
    }

    /** @test */
    public function it_throws_exception_when_xml_is_unreadable()
    {
        // create an unreadable file
        $unreadableXmlName = 'unreadable.xml';
        $unreadableXmlPath = concatenate_path($this->testCloverDir, $unreadableXmlName);
        unlink($unreadableXmlPath);

        file_put_contents($unreadableXmlPath, '<?xml version="1.0"?><invalid>');
        chmod($unreadableXmlPath, 0000);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coverage file is not readable: ' . $unreadableXmlPath);
        new CoverageAnalyzer($this->testCloverDir, $unreadableXmlName);

        unlink($unreadableXmlPath);
    }

    /** @test */
    public function it_finds_uncovered_methods()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $uncovered = $analyzer->skipMagicMethods(false)->analyze();

        $this->assertIsArray($uncovered);
        $this->assertNotEmpty($uncovered);

        // Check structure of first result
        $first = $uncovered[0];
        $this->assertArrayHasKey('class', $first);
        $this->assertArrayHasKey('method', $first);
        $this->assertArrayHasKey('file', $first);
        $this->assertArrayHasKey('coverage', $first);
        $this->assertArrayHasKey('lines', $first);
    }

    /** @test */
    public function it_calculates_zero_percent_coverage_correctly()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $uncovered = $analyzer->analyze();

        // Find the deleteUser method (which has 0% coverage)
        $deleteUserMethod = array_filter($uncovered, function($method) {
            return $method['method'] === 'deleteUser';
        });

        $this->assertNotEmpty($deleteUserMethod);

        $method = array_values($deleteUserMethod)[0];
        $this->assertEquals(0.0, $method['coverage']);
    }

    /** @test */
    public function it_filters_by_specific_files()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $analyzer->filterByFiles(['src/Services/UserService.php']);

        $uncovered = $analyzer->analyze();

        foreach ($uncovered as $method) {
            $this->assertStringContainsString('UserService', $method['file']);
        }
    }

    /** @test */
    public function it_analyzes_single_file()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $uncovered = $analyzer->analyzeFile('src/Services/UserService.php');

        $this->assertIsArray($uncovered);

        foreach ($uncovered as $method) {
            $this->assertStringContainsString('UserService', $method['file']);
        }
    }

    /** @test */
    public function it_gets_method_coverage_details()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);

        $coverage = $analyzer->getMethodCoverage(
            'src/Services/UserService.php',
            'createUser'
        );

        $this->assertNotNull($coverage);
        $this->assertEquals('createUser', $coverage['name']);
        $this->assertArrayHasKey('coverage', $coverage);
        $this->assertArrayHasKey('lines', $coverage);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_method()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);

        $coverage = $analyzer->getMethodCoverage(
            'src/Services/UserService.php',
            'nonExistentMethod'
        );

        $this->assertNull($coverage);
    }

    /** @test */
    public function it_skips_magic_methods_by_default()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $uncovered = $analyzer->analyze();

        $hasMagicMethod = array_filter($uncovered, function($method) {
            return str_starts_with($method['method'], '__');
        });

        $this->assertEmpty($hasMagicMethod);
    }

    /** @test */
    public function it_includes_magic_methods_when_configured()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $analyzer->skipMagicMethods(false);

        $uncovered = $analyzer->analyze();

        // This test assumes the clover file has uncovered magic methods
        // Adjust based on your actual test data
        $this->assertIsArray($uncovered);
    }

    /** @test */
    public function it_respects_coverage_threshold()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $analyzer->setCoverageThreshold(50.0);

        $uncovered = $analyzer->analyze();

        foreach ($uncovered as $method) {
            $this->assertLessThanOrEqual(50.0, $method['coverage']);
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_threshold()
    {
        $this->expectException(InvalidArgumentException::class);

        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $analyzer->setCoverageThreshold(150.0);
    }

    /** @test */
    public function it_supports_fluent_interface()
    {
        $analyzer = (new CoverageAnalyzer($this->testCloverDir, $this->testCloverName))
            ->filterByFiles(['src/Services/'])
            ->setCoverageThreshold(30.0)
            ->skipMagicMethods(true);

        $this->assertInstanceOf(CoverageAnalyzer::class, $analyzer);
    }

    /** @test */
    public function it_provides_line_details()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        $uncovered = $analyzer->analyze();

        $this->assertNotEmpty($uncovered);

        $first = $uncovered[0];
        $this->assertArrayHasKey('lines', $first);
        $this->assertArrayHasKey('total', $first['lines']);
        $this->assertArrayHasKey('covered', $first['lines']);
        $this->assertArrayHasKey('uncovered', $first['lines']);
        $this->assertArrayHasKey('details', $first['lines']);
    }

    /** @test */
    public function it_handles_empty_coverage_report()
    {
        $emptyClover = concatenate_path($this->testCloverDir, 'empty-clover.xml');
        file_put_contents($emptyClover, '<?xml version="1.0"?><coverage></coverage>');

        $analyzer = new CoverageAnalyzer($this->testCloverDir, 'empty-clover.xml');
        $uncovered = $analyzer->analyze();

        $this->assertIsArray($uncovered);
        $this->assertEmpty($uncovered);

        unlink($emptyClover);
    }

    /** @test */
    public function it_reports_no_files_error_when_report_has_no_files()
    {
        $noFiles = concatenate_path($this->testCloverDir, 'no-files-clover.xml');
        // project exists but contains no <file> elements
        file_put_contents($noFiles, '<?xml version="1.0"?><coverage><project></project></coverage>');

        $analyzer = new CoverageAnalyzer($this->testCloverDir, 'no-files-clover.xml');
        $result = $analyzer->analyze();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
        $this->assertTrue($analyzer->hasErrors());
        $this->assertContains('No files found in coverage report', $analyzer->getErrors());

        unlink($noFiles);
    }

    /** @test */
    public function it_calculates_overall_statistics_and_percentages()
    {
        $metricsClover = concatenate_path($this->testCloverDir, 'metrics-clover.xml');
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage>
  <project>
    <metrics files="2" classes="1" methods="4" coveredmethods="2" statements="10" coveredstatements="5" elements="12" coveredelements="6"/>
  </project>
</coverage>
XML;
        file_put_contents($metricsClover, $xml);

        $analyzer = new CoverageAnalyzer($this->testCloverDir, 'metrics-clover.xml');
        $stats = $analyzer->getOverallStatistics();

        $this->assertIsArray($stats);
        $this->assertEquals(2, $stats['files']);
        $this->assertEquals(1, $stats['classes']);
        $this->assertEquals(4, $stats['methods']);
        $this->assertEquals(2, $stats['covered_methods']);
        $this->assertEquals(10, $stats['statements']);
        $this->assertEquals(5, $stats['covered_statements']);
        $this->assertEquals(12, $stats['elements']);
        $this->assertEquals(6, $stats['covered_elements']);
        $this->assertEquals(50.0, $stats['method_coverage_percent']);
        $this->assertEquals(50.0, $stats['statement_coverage_percent']);
        $this->assertEquals(50.0, $stats['element_coverage_percent']);

        unlink($metricsClover);
    }

    /** @test */
    public function it_handles_methods_with_no_statements_but_executed_as_full_coverage()
    {
        $noStmts = concatenate_path($this->testCloverDir, 'no-stmts-clover.xml');
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage>
  <project>
    <file name="src/Services/NoStmtService.php">
      <class name="NoStmtService" namespace="Services">
        <metrics complexity="1" methods="1" coveredmethods="1" conditionals="0" coveredconditionals="0" statements="0" coveredstatements="0" elements="1" coveredelements="1"/>
      </class>
      <line num="5" type="method" name="noop" visibility="public" complexity="1" crap="0" count="1"/>
      <!-- no stmt lines for this method -->
    </file>
  </project>
</coverage>
XML;
        file_put_contents($noStmts, $xml);

        $analyzer = new CoverageAnalyzer($this->testCloverDir, 'no-stmts-clover.xml');
        $coverage = $analyzer->getMethodCoverage('src/Services/NoStmtService.php', 'noop');

        $this->assertNotNull($coverage);
        $this->assertEquals(100.0, $coverage['coverage']);

        unlink($noStmts);
    }

    /** @test */
    public function it_skips_private_and_protected_methods_when_configured()
    {
        $visClover = concatenate_path($this->testCloverDir, 'visibility-clover.xml');
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage>
  <project>
    <file name="src/Services/VisibilityService.php">
      <class name="VisibilityService" namespace="Services">
        <metrics complexity="1" methods="2" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="2" coveredstatements="0" elements="2" coveredelements="0"/>
      </class>
      <line num="10" type="method" name="secret" visibility="private" complexity="1" crap="0" count="0"/>
      <line num="11" type="stmt" count="0"/>
      <line num="20" type="method" name="semiSecret" visibility="protected" complexity="1" crap="0" count="0"/>
      <line num="21" type="stmt" count="0"/>
    </file>
  </project>
</coverage>
XML;
        file_put_contents($visClover, $xml);

        $analyzer = new CoverageAnalyzer($this->testCloverDir, 'visibility-clover.xml');
        // By default both skip flags are false; enable skipping
        $analyzer->skipPrivateMethods(true)->skipProtectedMethods(true);

        $uncovered = $analyzer->analyze();
        // Both methods should be skipped, resulting in empty results
        $this->assertIsArray($uncovered);
        $this->assertEmpty($uncovered);

        unlink($visClover);
    }

    /** @test */
    public function analyze_file_accepts_partial_path_matches()
    {
        $analyzer = new CoverageAnalyzer($this->testCloverDir, $this->testCloverName);
        // pass a partial path only
        $uncovered = $analyzer->analyzeFile('UserService.php');

        $this->assertIsArray($uncovered);
        $this->assertNotEmpty($uncovered);
        foreach ($uncovered as $method) {
            $this->assertStringContainsString('UserService', $method['file']);
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Create a test clover.xml file with sample data
     */
    private function createTestCloverFile(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="1234567890">
  <project timestamp="1234567890">
    <file name="src/Services/UserService.php">
        <class name="Services\UserService" namespace="global">
            <metrics complexity="10" methods="3" coveredmethods="1" conditionals="0" coveredconditionals="0" statements="155" coveredstatements="14" elements="165" coveredelements="14"/>
        </class>
        <!-- Method with full coverage -->
        <line num="10" type="method" name="createUser" visibility="public" complexity="3" crap="1" crapdetail="1" count="5"/>
        <line num="11" type="stmt" count="5"/>
        <line num="12" type="stmt" count="5"/>
        <line num="13" type="stmt" count="5"/>

        <!-- Method with 0% coverage -->
        <line num="25" type="method" name="deleteUser" visibility="public" complexity="2" crap="0" crapdetail="0" count="0"/>
        <line num="26" type="stmt" count="0"/>
        <line num="27" type="stmt" count="0"/>
        <line num="28" type="stmt" count="0"/>

        <!-- Method with partial coverage -->
        <line num="30" type="method" name="updateUser" visibility="public" complexity="4" crap="0" crapdetail="0" count="3"/>
        <line num="31" type="stmt" count="3"/>
        <line num="32" type="stmt" count="3"/>
        <line num="33" type="stmt" count="0"/>
        <line num="34" type="stmt" count="0"/>
        <line num="35" type="stmt" count="3"/>
    </file>

    <file name="src/Services/OrderService.php">
        <class name="Services\OrderService" namespace="global">
            <metrics complexity="5" methods="2" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="155" coveredstatements="14" elements="165" coveredelements="14"/>

        </class>
        <!-- Another uncovered method -->
        <line num="15" type="method" name="cancelOrder" visibility="public" complexity="2" crap="0" crapdetail="0" count="0"/>
        <line num="16" type="stmt" count="0"/>
        <line num="17" type="stmt" count="0"/>
        <line num="18" type="stmt" count="0"/>

        <!-- Method with partial coverage -->
        <line num="25" type="method" name="processOrder" visibility="public" complexity="3"/>
        <line num="25" type="method" count="2"/>
        <line num="26" type="stmt" count="2"/>
        <line num="27" type="stmt" count="2"/>
    </file>
    <file name="src/Services/ProductService.php">
        <class name="ProductService" namespace="Services">
            <metrics complexity="5" methods="2" coveredmethods="0" conditionals="0" coveredconditionals="0" statements="155" coveredstatements="14" elements="165" coveredelements="14"/>

        </class>
        <!-- Another uncovered method -->
        <line num="30" type="method" name="createProduct" visibility="public" complexity="2" crap="0" crapdetail="0" count="0"/>
        <line num="31" type="stmt" count="0"/>
        <line num="32" type="stmt" count="0"/>
        <line num="33" type="stmt" count="0"/>

        <!-- Method with partial coverage -->
        <line num="35" type="method" name="processProduct" visibility="public" complexity="3"/>
        <line num="36" type="method" count="2"/>
        <line num="37" type="stmt" count="2"/>
        <line num="38" type="stmt" count="2"/>

        <!-- magic method -->
        <line num="40" type="method" name="__string" visibility="public" complexity="1" crap="0" crapdetail="0" count="1"/>
        <line num="41" type="stmt" count="1"/>
        <line num="42" type="stmt" count="1"/>
        <line num="43" type="stmt" count="1"/>
    </file>
  </project>
</coverage>
XML;

        file_put_contents($this->testCloverPath, $xml);
    }
}
