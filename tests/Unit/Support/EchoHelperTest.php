<?php

namespace Tests\Unit\Support;

use Modules\Core\Support\EchoHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(EchoHelper::class)]
class EchoHelperTest extends UnitTestCase
{
    #[Test]
    public function itEscapesHtmlSpecialChars(): void
    {
        $input = '<script>alert("xss")</script>';
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertSame('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $result);
    }

    #[Test]
    public function itReturnsNullForNullInput(): void
    {
        $result = EchoHelper::htmlsc(null);
        
        $this->assertNull($result);
    }

    #[Test]
    public function itHandlesQuotesInHtmlsc(): void
    {
        $input = "It's a \"test\"";
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertStringContainsString('&#039;', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    #[Test]
    public function itHandlesAmpersands(): void
    {
        $input = 'Johnson & Johnson';
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertSame('Johnson &amp; Johnson', $result);
    }

    #[Test]
    #[DataProvider('specialCharsProvider')]
    public function itEscapesVariousSpecialChars(string $input, string $expectedContains): void
    {
        $result = EchoHelper::htmlsc($input);
        
        $this->assertStringContainsString($expectedContains, $result);
    }

    public static function specialCharsProvider(): array
    {
        return [
            'less than' => ['<test>', '&lt;'],
            'greater than' => ['test>', '&gt;'],
            'double quote' => ['"quoted"', '&quot;'],
            'single quote' => ["it's", '&#039;'],
            'ampersand' => ['A & B', '&amp;'],
        ];
    }

    #[Test]
    public function itOutputsEscapedHtmlChars(): void
    {
        $input = '<b>Bold</b>';
        
        ob_start();
        EchoHelper::_htmlsc($input);
        $output = ob_get_clean();
        
        $this->assertSame('&lt;b&gt;Bold&lt;/b&gt;', $output);
    }

    #[Test]
    public function itReturnsEmptyStringForNullHtmlscOutput(): void
    {
        ob_start();
        $result = EchoHelper::_htmlsc(null);
        $output = ob_get_clean();
        
        $this->assertSame('', $result);
        $this->assertSame('', $output);
    }

    #[Test]
    public function itOutputsHtmlEntities(): void
    {
        $input = '<script>test</script>';
        
        ob_start();
        EchoHelper::_htmle($input);
        $output = ob_get_clean();
        
        $this->assertStringContainsString('&lt;', $output);
        $this->assertStringContainsString('&gt;', $output);
    }

    #[Test]
    public function itReturnsEmptyStringForNullHtmleOutput(): void
    {
        ob_start();
        $result = EchoHelper::_htmle(null);
        $output = ob_get_clean();
        
        $this->assertSame('', $result);
        $this->assertSame('', $output);
    }

    #[Test]
    public function itHandlesEmptyStrings(): void
    {
        $result = EchoHelper::htmlsc('');
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function itPreservesSafeText(): void
    {
        $input = 'This is safe text without special chars';
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertSame($input, $result);
    }

    #[Test]
    public function itHandlesUnicodeCharacters(): void
    {
        $input = 'Hello 世界 🌍';
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('世界', $result);
    }

    #[Test]
    public function itHandlesNumericStrings(): void
    {
        $input = '12345.67';
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertSame('12345.67', $result);
    }

    #[Test]
    public function itHandlesMultipleSpecialChars(): void
    {
        $input = '<div class="test" id=\'myId\'>Content & more</div>';
        
        $result = EchoHelper::htmlsc($input);
        
        $this->assertStringContainsString('&lt;', $result);
        $this->assertStringContainsString('&gt;', $result);
        $this->assertStringContainsString('&quot;', $result);
        $this->assertStringContainsString('&#039;', $result);
        $this->assertStringContainsString('&amp;', $result);
    }
}