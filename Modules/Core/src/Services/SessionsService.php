<?php

namespace Modules\Core\Services;

use AllowDynamicProperties;
use Modules\Core\Libraries\Crypt;
use Modules\Core\Models\User;

#[AllowDynamicProperties]
class SessionsService extends BaseService
{
    /**
     * Authenticate a user by email and password, upgrade legacy MD5 password hashes when needed, and initialize session data on success.
     *
     * If the user record contains an old MD5 password, a successful MD5 verification will replace it with the current salted hash format before continuing.
     *
     * @param string $email    the user's email address used to locate the account
     * @param string $password the plaintext password to verify
     *
     * @return bool `true` if authentication succeeds and session data is set (session contains `user_type`, `user_id`, `user_name`, `user_email`, `user_company`, and `user_language`), `false` otherwise
     */
    public function auth($email, $password): bool
    {
        $user = User::query()->where('user_email', $email)->first();

        if ($user) {
            if ((new Crypt())->check_password($user->user_password, $password)) {
                $session_data = ['user_type' => $user->user_type, 'user_id' => $user->user_id, 'user_name' => $user->user_name, 'user_email' => $user->user_email, 'user_company' => $user->user_company, 'user_language' => $user->user_language ?? 'system'];

                session()->put($session_data);

                return true;
            }
        }

        return false;
    }
}
