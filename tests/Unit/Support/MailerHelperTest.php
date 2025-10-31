<?php

namespace Tests\Unit\Support;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Setting;
use Modules\Core\Support\MailerHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(MailerHelper::class)]
class MailerHelperTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        DB::table('ip_settings')->delete();
    }

    #[Test]
    public function it_detects_phpmail_configuration(): void
    {
        Setting::setValue('email_send_method', 'phpmail');
        
        $result = MailerHelper::mailer_configured();
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_detects_sendmail_configuration(): void
    {
        Setting::setValue('email_send_method', 'sendmail');
        
        $result = MailerHelper::mailer_configured();
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_detects_smtp_configuration_with_server(): void
    {
        Setting::setValue('email_send_method', 'smtp');
        Setting::setValue('smtp_server_address', 'smtp.example.com');
        
        $result = MailerHelper::mailer_configured();
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_detects_incomplete_smtp_configuration(): void
    {
        Setting::setValue('email_send_method', 'smtp');
        Setting::setValue('smtp_server_address', '');
        
        $result = MailerHelper::mailer_configured();
        
        $this->assertFalse($result);
    }

    #[Test]
    public function it_detects_no_configuration(): void
    {
        $result = MailerHelper::mailer_configured();
        
        $this->assertFalse($result);
    }

    #[Test]
    public function it_validates_single_email_address(): void
    {
        $result = MailerHelper::validate_email_address('test@example.com');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_validates_multiple_email_addresses(): void
    {
        $result = MailerHelper::validate_email_address('test1@example.com,test2@example.com');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_rejects_invalid_email(): void
    {
        $result = MailerHelper::validate_email_address('invalid-email');
        
        $this->assertFalse($result);
    }

    #[Test]
    public function it_rejects_list_with_one_invalid_email(): void
    {
        $result = MailerHelper::validate_email_address('valid@example.com,invalid-email');
        
        $this->assertFalse($result);
    }

    #[Test]
    #[DataProvider('emailValidationProvider')]
    public function it_validates_various_email_formats(string $email, bool $expected): void
    {
        $result = MailerHelper::validate_email_address($email);
        
        $this->assertSame($expected, $result);
    }

    public static function emailValidationProvider(): array
    {
        return [
            'valid simple' => ['user@example.com', true],
            'valid with subdomain' => ['user@mail.example.com', true],
            'valid with plus' => ['user+tag@example.com', true],
            'valid with numbers' => ['user123@example.com', true],
            'invalid no @' => ['userexample.com', false],
            'invalid no domain' => ['user@', false],
            'invalid no username' => ['@example.com', false],
            'invalid spaces' => ['user @example.com', false],
            'valid multiple' => ['a@b.com,c@d.com', true],
            'invalid in list' => ['a@b.com,invalid', false],
        ];
    }

    #[Test]
    public function it_validates_email_with_dots(): void
    {
        $result = MailerHelper::validate_email_address('first.last@example.com');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_validates_email_with_hyphens(): void
    {
        $result = MailerHelper::validate_email_address('user-name@example.com');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_validates_email_with_underscores(): void
    {
        $result = MailerHelper::validate_email_address('user_name@example.com');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_rejects_email_with_spaces(): void
    {
        $result = MailerHelper::validate_email_address('user name@example.com');
        
        $this->assertFalse($result);
    }

    #[Test]
    public function it_rejects_email_with_double_at(): void
    {
        $result = MailerHelper::validate_email_address('user@@example.com');
        
        $this->assertFalse($result);
    }

    #[Test]
    public function it_validates_multiple_emails_with_spaces_after_comma(): void
    {
        $result = MailerHelper::validate_email_address('a@b.com, c@d.com');
        
        // Note: This might fail since filter_var doesn't trim
        $this->assertFalse($result);
    }

    #[Test]
    public function it_validates_email_with_country_code_tld(): void
    {
        $result = MailerHelper::validate_email_address('user@example.co.uk');
        
        $this->assertTrue($result);
    }

    #[Test]
    public function it_validates_email_with_new_tlds(): void
    {
        $result = MailerHelper::validate_email_address('user@example.technology');
        
        $this->assertTrue($result);
    }
}