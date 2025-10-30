<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Entities\User;

/**
 * UserHelper
 * 
 * Static helper class converted from procedural functions.
 */
class UserHelper
{
    /**
     * @param mixed id or object $user - since 1.6.3
     */
    public static function format_user($user): string
    {
        // Get user by ID if numeric
        if ($user && is_numeric($user)) {
            $user = User::find($user);
        }
    
        // Not exist or found, Stop.
        if (empty($user->user_name ?? null)) {
            return '';
        }
    
        $user_company = empty($user->user_company) ? '' : ' - ' . $user->user_company;
        $contact      = empty($user->user_invoicing_contact) ? '' : ' - ' . $user->user_invoicing_contact;
    
        return ucfirst($user->user_name) . $user_company . $contact;
    }

}
