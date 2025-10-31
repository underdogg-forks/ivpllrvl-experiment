<?php

namespace Tests\Unit\Support;

use Modules\Core\Support\ClientHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(ClientHelper::class)]
class ClientHelperTest extends UnitTestCase
{
    #[Test]
    public function itFormatsGenderMale(): void
    {
        $result = ClientHelper::format_gender(0);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function itFormatsGenderFemale(): void
    {
        $result = ClientHelper::format_gender(1);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function itFormatsGenderOther(): void
    {
        $result = ClientHelper::format_gender(2);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    #[DataProvider('genderProvider')]
    public function itFormatsVariousGenders($gender): void
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
    public function itHandlesStringGenderValues(): void
    {
        $result = ClientHelper::format_gender('0');
        
        $this->assertIsString($result);
    }

    #[Test]
    public function itHandlesNullGender(): void
    {
        $result = ClientHelper::format_gender(null);
        
        $this->assertIsString($result);
    }
}