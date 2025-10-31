<?php

namespace Tests\Unit\Support;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\User;
use Modules\Core\Support\UserHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(UserHelper::class)]
class UserHelperTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        DB::table('ip_users')->delete();
    }

    #[Test]
    public function itReturnsEmptyStringForNullUser(): void
    {
        $result = UserHelper::format_user(null);
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function itReturnsEmptyStringForNonexistentUserId(): void
    {
        $result = UserHelper::format_user(99999);
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function itFormatsUserWithNameOnly(): void
    {
        $user = (object)[
            'user_name' => 'John Doe',
            'user_company' => '',
            'user_invoicing_contact' => ''
        ];
        
        $result = UserHelper::format_user($user);
        
        $this->assertSame('John doe', $result);
    }

    #[Test]
    public function itFormatsUserWithCompany(): void
    {
        $user = (object)[
            'user_name' => 'John Doe',
            'user_company' => 'ACME Corp',
            'user_invoicing_contact' => ''
        ];
        
        $result = UserHelper::format_user($user);
        
        $this->assertSame('John doe - ACME Corp', $result);
    }

    #[Test]
    public function itFormatsUserWithContact(): void
    {
        $user = (object)[
            'user_name' => 'John Doe',
            'user_company' => '',
            'user_invoicing_contact' => 'jane@example.com'
        ];
        
        $result = UserHelper::format_user($user);
        
        $this->assertSame('John doe - jane@example.com', $result);
    }

    #[Test]
    public function itFormatsUserWithAllFields(): void
    {
        $user = (object)[
            'user_name' => 'John Doe',
            'user_company' => 'ACME Corp',
            'user_invoicing_contact' => 'jane@example.com'
        ];
        
        $result = UserHelper::format_user($user);
        
        $this->assertSame('John doe - ACME Corp - jane@example.com', $result);
    }

    #[Test]
    public function itCapitalizesFirstLetterOfName(): void
    {
        $user = (object)[
            'user_name' => 'john',
            'user_company' => '',
            'user_invoicing_contact' => ''
        ];
        
        $result = UserHelper::format_user($user);
        
        $this->assertStringStartsWith('John', $result);
    }

    #[Test]
    public function itHandlesUserObjectWithoutOptionalFields(): void
    {
        $user = (object)[
            'user_name' => 'Jane Smith'
        ];
        
        $result = UserHelper::format_user($user);
        
        $this->assertSame('Jane smith', $result);
    }
}