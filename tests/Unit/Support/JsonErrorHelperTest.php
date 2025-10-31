<?php

namespace Tests\Unit\Support;

use Modules\Core\Support\JsonErrorHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(JsonErrorHelper::class)]
class JsonErrorHelperTest extends UnitTestCase
{
    #[Test]
    public function it_returns_empty_array_when_no_post_data(): void
    {
        $_POST = [];
        
        $result = JsonErrorHelper::json_errors();
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function it_returns_array_of_errors(): void
    {
        // Simulate POST data
        $_POST = ['field1' => 'value1', 'field2' => 'value2'];
        
        $result = JsonErrorHelper::json_errors();
        
        $this->assertIsArray($result);
    }

    #[Test]
    public function it_processes_multiple_post_fields(): void
    {
        $_POST = [
            'email' => 'invalid-email',
            'name' => 'John Doe',
            'password' => 'short'
        ];
        
        $result = JsonErrorHelper::json_errors();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('password', $result);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }
}