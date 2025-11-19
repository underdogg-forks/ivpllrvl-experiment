<?php

namespace Modules\Core\Hooks;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class SetTimezoneClass
{
    /**
     * Set UTC as the current timezone if no one was set in the PHP ini.
     */
    public function setTimezone()
    {
        if ( ! ini_get('date.timezone')) {
            date_default_timezone_set('UTC');
        }
    }
}
