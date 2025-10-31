<?php

namespace Tests\Unit\Support;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Setting;
use Modules\Core\Support\NumberHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(NumberHelper::class)]
class NumberHelperTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up settings table
        DB::table('ip_settings')->delete();
    }

    #[Test]
    public function itFormatsCurrencyWithDefaultSettings(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::format_currency(1234.56);
        
        $this->assertSame('$1,234.56', $result);
    }

    #[Test]
    public function itFormatsCurrencyWithSymbolAfter(): void
    {
        Setting::setValue('currency_symbol', '€');
        Setting::setValue('currency_symbol_placement', 'after');
        Setting::setValue('thousands_separator', '.');
        Setting::setValue('decimal_point', ',');
        Setting::setValue('tax_rate_decimal_places', '2');
        
        $result = NumberHelper::format_currency(1234.56);
        
        $this->assertSame('1.234,56€', $result);
    }

    #[Test]
    public function itFormatsCurrencyWithSymbolAfterSpace(): void
    {
        Setting::setValue('currency_symbol', '€');
        Setting::setValue('currency_symbol_placement', 'afterspace');
        Setting::setValue('thousands_separator', ' ');
        Setting::setValue('decimal_point', ',');
        Setting::setValue('tax_rate_decimal_places', '2');
        
        $result = NumberHelper::format_currency(1234.56);
        
        $this->assertSame('1 234,56&nbsp;€', $result);
    }

    #[Test]
    public function itFormatsCurrencyWithZeroDecimals(): void
    {
        Setting::setValue('currency_symbol', '$');
        Setting::setValue('currency_symbol_placement', 'before');
        Setting::setValue('thousands_separator', ',');
        Setting::setValue('decimal_point', '');
        Setting::setValue('tax_rate_decimal_places', '0');
        
        $result = NumberHelper::format_currency(1234.56);
        
        $this->assertSame('$1,235', $result);
    }

    #[Test]
    #[DataProvider('currencyAmountProvider')]
    public function itFormatsVariousCurrencyAmounts($amount, string $expected): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::format_currency($amount);
        
        $this->assertSame($expected, $result);
    }

    public static function currencyAmountProvider(): array
    {
        return [
            'zero' => [0, '$0.00'],
            'small amount' => [0.99, '$0.99'],
            'negative amount' => [-1234.56, '$-1,234.56'],
            'large amount' => [1234567.89, '$1,234,567.89'],
            'string numeric' => ['1234.56', '$1,234.56'],
        ];
    }

    #[Test]
    public function itFormatsAmountWithDefaultSettings(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::format_amount(1234.56);
        
        $this->assertSame('1,234.56', $result);
    }

    #[Test]
    public function itReturnsNullForNullAmount(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::format_amount(null);
        
        $this->assertNull($result);
    }

    #[Test]
    public function itFormatsAmountWithEuropeanFormat(): void
    {
        Setting::setValue('thousands_separator', '.');
        Setting::setValue('decimal_point', ',');
        Setting::setValue('tax_rate_decimal_places', '2');
        
        $result = NumberHelper::format_amount(1234.56);
        
        $this->assertSame('1.234,56', $result);
    }

    #[Test]
    public function itFormatsQuantityWithDefaultSettings(): void
    {
        Setting::setValue('thousands_separator', ',');
        Setting::setValue('decimal_point', '.');
        Setting::setValue('default_item_decimals', '2');
        
        $result = NumberHelper::format_quantity(123.456);
        
        $this->assertSame('123.46', $result);
    }

    #[Test]
    public function itFormatsQuantityWithHigherPrecision(): void
    {
        Setting::setValue('thousands_separator', ',');
        Setting::setValue('decimal_point', '.');
        Setting::setValue('default_item_decimals', '4');
        
        $result = NumberHelper::format_quantity(123.456789);
        
        $this->assertSame('123.4568', $result);
    }

    #[Test]
    public function itReturnsNullForNullQuantity(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::format_quantity(null);
        
        $this->assertNull($result);
    }

    #[Test]
    public function itStandardizesAmountFromEuropeanFormat(): void
    {
        Setting::setValue('thousands_separator', '.');
        Setting::setValue('decimal_point', ',');
        
        $result = NumberHelper::standardize_amount('1.234,56');
        
        $this->assertSame('1234.56', $result);
    }

    #[Test]
    public function itStandardizesAmountFromUsFormat(): void
    {
        Setting::setValue('thousands_separator', ',');
        Setting::setValue('decimal_point', '.');
        
        $result = NumberHelper::standardize_amount('1,234.56');
        
        $this->assertSame('1234.56', $result);
    }

    #[Test]
    public function itHandlesNumericAmountStandardization(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::standardize_amount(1234.56);
        
        $this->assertSame(1234.56, $result);
    }

    #[Test]
    public function itStandardizesAmountWithMultipleDots(): void
    {
        Setting::setValue('thousands_separator', '.');
        Setting::setValue('decimal_point', ',');
        
        // European format with multiple dots for thousands
        $result = NumberHelper::standardize_amount('1.234.567,89');
        
        $this->assertSame('1234567.89', $result);
    }

    #[Test]
    public function itHandlesEmptyThousandsSeparator(): void
    {
        Setting::setValue('thousands_separator', '');
        Setting::setValue('decimal_point', ',');
        
        $result = NumberHelper::standardize_amount('1234,56');
        
        $this->assertSame('1234.56', $result);
    }

    #[Test]
    public function itReturnsNullForNullStandardizeAmount(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::standardize_amount(null);
        
        $this->assertNull($result);
    }

    #[Test]
    public function itStandardizesZero(): void
    {
        $this->setDefaultCurrencySettings();
        
        $result = NumberHelper::standardize_amount('0,00');
        
        $this->assertSame('0.00', $result);
    }

    #[Test]
    public function itStandardizesNegativeAmounts(): void
    {
        Setting::setValue('thousands_separator', ',');
        Setting::setValue('decimal_point', '.');
        
        $result = NumberHelper::standardize_amount('-1,234.56');
        
        $this->assertSame('-1234.56', $result);
    }

    private function setDefaultCurrencySettings(): void
    {
        Setting::setValue('currency_symbol', '$');
        Setting::setValue('currency_symbol_placement', 'before');
        Setting::setValue('thousands_separator', ',');
        Setting::setValue('decimal_point', '.');
        Setting::setValue('tax_rate_decimal_places', '2');
        Setting::setValue('default_item_decimals', '2');
    }
}