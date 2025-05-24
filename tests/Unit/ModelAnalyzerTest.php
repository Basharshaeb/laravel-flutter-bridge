<?php

namespace LaravelFlutter\Generator\Tests\Unit;

use LaravelFlutter\Generator\Tests\TestCase;
use LaravelFlutter\Generator\Analyzers\ModelAnalyzer;
use LaravelFlutter\Generator\Tests\Fixtures\TestUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModelAnalyzerTest extends TestCase
{
    protected ModelAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->analyzer = new ModelAnalyzer();
        $this->createTestTable();
    }

    protected function createTestTable(): void
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->integer('age')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->text('bio')->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_can_analyze_returns_true_for_eloquent_model(): void
    {
        $this->assertTrue($this->analyzer->canAnalyze(TestUser::class));
        $this->assertTrue($this->analyzer->canAnalyze(new TestUser()));
    }

    public function test_can_analyze_returns_false_for_non_model(): void
    {
        $this->assertFalse($this->analyzer->canAnalyze('NonExistentClass'));
        $this->assertFalse($this->analyzer->canAnalyze(new \stdClass()));
    }

    public function test_analyze_model_returns_correct_structure(): void
    {
        $result = $this->analyzer->analyzeModel(TestUser::class);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('class_name', $result);
        $this->assertArrayHasKey('full_class_name', $result);
        $this->assertArrayHasKey('table_name', $result);
        $this->assertArrayHasKey('primary_key', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('fillable', $result);
        $this->assertArrayHasKey('timestamps', $result);

        $this->assertEquals('TestUser', $result['class_name']);
        $this->assertEquals(TestUser::class, $result['full_class_name']);
        $this->assertEquals('test_users', $result['table_name']);
        $this->assertEquals('id', $result['primary_key']);
        $this->assertTrue($result['timestamps']);
    }

    public function test_get_model_attributes_returns_correct_types(): void
    {
        $attributes = $this->analyzer->getModelAttributes(TestUser::class);

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('id', $attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('email', $attributes);
        $this->assertArrayHasKey('is_active', $attributes);
        $this->assertArrayHasKey('age', $attributes);
        $this->assertArrayHasKey('balance', $attributes);

        // Check type mappings
        $this->assertEquals('int', $attributes['id']['type']);
        $this->assertEquals('String', $attributes['name']['type']);
        $this->assertEquals('String', $attributes['email']['type']);
        $this->assertEquals('bool', $attributes['is_active']['type']);
        $this->assertEquals('int', $attributes['age']['type']);
        $this->assertEquals('double', $attributes['balance']['type']);

        // Check nullable flags
        $this->assertFalse($attributes['id']['nullable']);
        $this->assertFalse($attributes['name']['nullable']);
        $this->assertTrue($attributes['age']['nullable']);
    }

    public function test_get_model_relationships_returns_array(): void
    {
        $relationships = $this->analyzer->getModelRelationships(TestUser::class);

        $this->assertIsArray($relationships);
        // Since TestUser doesn't have relationships, it should be empty
        // In a real test, you'd create a model with relationships
    }

    public function test_analyze_with_instance_works(): void
    {
        $user = new TestUser();
        $result = $this->analyzer->analyze($user);

        $this->assertIsArray($result);
        $this->assertEquals('TestUser', $result['class_name']);
    }

    public function test_analyze_with_string_works(): void
    {
        $result = $this->analyzer->analyze(TestUser::class);

        $this->assertIsArray($result);
        $this->assertEquals('TestUser', $result['class_name']);
    }
}
