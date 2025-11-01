<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Support\ClientHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[CoversClass(ClientHelper::class)]
class ClientHelperTest extends UnitTestCase
{
    #[Test]
    public function it_formats_gender_male(): void
    {
        $result = ClientHelper::format_gender(0);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_formats_gender_female(): void
    {
        $result = ClientHelper::format_gender(1);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_formats_gender_other(): void
    {
        $result = ClientHelper::format_gender(2);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    #[DataProvider('genderProvider')]
    public function it_formats_various_genders($gender): void
    {
        $result = ClientHelper::format_gender($gender);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public static function genderProvider(): array
    {
        return [
            'male' => [0],
            'female' => [1],
            'other' => [2],
            'unknown' => [99],
        ];
    }

    #[Test]
    public function it_handles_string_gender_values(): void
    {
        $result = ClientHelper::format_gender('0');
        
        $this->assertIsString($result);
    }

    #[Test]
    public function it_handles_null_gender(): void
    {
        $result = ClientHelper::format_gender(null);
        
        $this->assertIsString($result);
    }
}