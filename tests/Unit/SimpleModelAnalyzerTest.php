<?php

namespace BasharShaeb\LaravelFlutterGenerator\Tests\Unit;

use BasharShaeb\LaravelFlutterGenerator\Tests\TestCase;
use BasharShaeb\LaravelFlutterGenerator\Analyzers\ModelAnalyzer;

class SimpleModelAnalyzerTest extends TestCase
{
    protected ModelAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new ModelAnalyzer();
    }

    public function test_analyzer_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ModelAnalyzer::class, $this->analyzer);
    }

    public function test_can_analyze_returns_true_for_valid_class(): void
    {
        $this->assertTrue($this->analyzer->canAnalyze('App\Models\User'));
    }

    public function test_can_analyze_returns_false_for_invalid_class(): void
    {
        $this->assertFalse($this->analyzer->canAnalyze('NonExistentClass'));
    }

    public function test_get_dart_type_converts_correctly(): void
    {
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($this->analyzer);
        $method = $reflection->getMethod('getDartType');
        $method->setAccessible(true);

        $this->assertEquals('int', $method->invoke($this->analyzer, 'integer'));
        $this->assertEquals('String', $method->invoke($this->analyzer, 'string'));
        $this->assertEquals('bool', $method->invoke($this->analyzer, 'boolean'));
        $this->assertEquals('double', $method->invoke($this->analyzer, 'decimal'));
        $this->assertEquals('DateTime', $method->invoke($this->analyzer, 'datetime'));
        $this->assertEquals('Map<String, dynamic>', $method->invoke($this->analyzer, 'json'));
    }

    public function test_analyzer_handles_exceptions_gracefully(): void
    {
        // Test that the analyzer doesn't crash on invalid input
        $result = $this->analyzer->canAnalyze('');
        $this->assertFalse($result);
    }
}
