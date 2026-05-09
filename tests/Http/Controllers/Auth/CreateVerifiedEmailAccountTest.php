<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\CreateVerifiedEmailAccount;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Test class that uses CreateVerifiedEmailAccount trait for testing.
 */
class TestCreateVerifiedEmailAccount
{
    use CreateVerifiedEmailAccount;

    public function exposeNormalizeName(?string $name): ?string
    {
        return $this->normalizeName($name);
    }

    public function exposeRules(): array
    {
        return $this->rules();
    }

    public function exposeCredentials(Request $request): array
    {
        return $this->credentials($request);
    }
}

class CreateVerifiedEmailAccountTest extends TestCase
{
    protected TestCreateVerifiedEmailAccount $trait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trait = new TestCreateVerifiedEmailAccount;
    }

    /** @test */
    public function it_normalizes_name_by_trimming(): void
    {
        $this->assertEquals('John Doe', $this->trait->exposeNormalizeName('  John Doe  '));
    }

    /** @test */
    public function it_returns_empty_for_empty_name(): void
    {
        $this->assertEquals('', $this->trait->exposeNormalizeName(''));
        $this->assertNull($this->trait->exposeNormalizeName(null));
    }

    /** @test */
    public function it_returns_same_string_when_no_trimming_needed(): void
    {
        $this->assertEquals('John', $this->trait->exposeNormalizeName('John'));
    }

    /** @test */
    public function it_has_required_rules_for_registration(): void
    {
        $rules = $this->trait->exposeRules();

        $this->assertArrayHasKey('token', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('surname', $rules);
        $this->assertArrayHasKey('company', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertStringContainsString('required', is_array($rules['token']) ? implode('|', $rules['token']) : $rules['token']);
        $this->assertStringContainsString('required', is_array($rules['email']) ? implode('|', $rules['email']) : $rules['email']);
    }

    /** @test */
    public function it_extracts_credentials_from_request(): void
    {
        $request = Request::create('/test', 'POST', [
            'email' => 'test@example.com',
            'name' => 'John',
            'surname' => 'Doe',
            'company' => 'Acme',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'token' => 'abc123',
        ]);

        $credentials = $this->trait->exposeCredentials($request);

        $this->assertEquals('test@example.com', $credentials['email']);
        $this->assertEquals('John', $credentials['name']);
        $this->assertEquals('Doe', $credentials['surname']);
        $this->assertEquals('Acme', $credentials['company']);
        $this->assertEquals('secret', $credentials['password']);
        $this->assertEquals('abc123', $credentials['token']);
    }
}
