<?php

namespace Tests\Unit\Support;

use Modules\Core\Support\CustomValuesHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(CustomValuesHelper::class)]
class CustomValuesHelperTest extends UnitTestCase
{
    #[Test]
    public function itReturnsEmptyStringForNullText(): void
    {
        $result = CustomValuesHelper::format_text(null);
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function itFormatsText(): void
    {
        $result = CustomValuesHelper::format_text('Sample text');
        
        $this->assertSame('Sample text', $result);
    }

    #[Test]
    public function itPreservesTextUnchanged(): void
    {
        $text = 'This is some <b>formatted</b> text';
        
        $result = CustomValuesHelper::format_text($text);
        
        $this->assertSame($text, $result);
    }

    #[Test]
    public function itFormatsBooleanTrue(): void
    {
        $result = CustomValuesHelper::format_boolean('1');
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function itFormatsBooleanFalse(): void
    {
        $result = CustomValuesHelper::format_boolean('0');
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function itReturnsEmptyStringForNullBoolean(): void
    {
        $result = CustomValuesHelper::format_boolean(null);
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function itReturnsEmptyStringForInvalidBoolean(): void
    {
        $result = CustomValuesHelper::format_boolean('invalid');
        
        $this->assertSame('', $result);
    }

    #[Test]
    #[DataProvider('booleanProvider')]
    public function itFormatsVariousBooleanValues($value, bool $isEmpty): void
    {
        $result = CustomValuesHelper::format_boolean($value);
        
        if ($isEmpty) {
            $this->assertSame('', $result);
            return;
        }
        $this->assertNotEmpty($result);
    }

    public static function booleanProvider(): array
    {
        return [
            'true string' => ['1', false],
            'false string' => ['0', false],
            'null' => [null, true],
            'empty string' => ['', true],
            'invalid' => ['invalid', true],
            'numeric 2' => ['2', true],
        ];
    }

    #[Test]
    public function itHandlesEmptyStringsInFormatText(): void
    {
        $result = CustomValuesHelper::format_text('');
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function itHandlesWhitespaceInFormatText(): void
    {
        $result = CustomValuesHelper::format_text('   ');
        
        $this->assertSame('   ', $result);
    }

    #[Test]
    public function itHandlesNumericStringsInFormatText(): void
    {
        $result = CustomValuesHelper::format_text('12345');
        
        $this->assertSame('12345', $result);
    }
}