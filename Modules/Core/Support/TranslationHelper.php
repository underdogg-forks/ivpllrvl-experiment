<?php

namespace Modules\Core\Support;

use Modules\Core\Models\Setting;

/**
 * Translation Helper Class.
 *
 * Provides static methods for language translation and management.
 * Now uses Laravel's translation system.
 */
class TranslationHelper
{
    /**
     * Output a language string, supports language fallback if a string wasn't found.
     *
     * Note: This now uses Laravel's translation system.
     * Translation files should be in resources/lang/
     *
     * @origin Modules/Core/Helpers/trans_helper.php
     */
    public static function trans($line, ?string $id = '', $default = null): string
    {
        // Use Laravel's translation system
        $lang_string = __($line, [], Setting::getValue('default_language') ?? config('app.locale', 'en'));

        // If translation key not found, Laravel returns the key itself
        // Check if we got back the same key (meaning no translation exists)
        if ($lang_string === $line && $default !== null) {
            $lang_string = $default;
        }

        if ($id != '') {
            $lang_string = '<label for="' . $id . '">' . $lang_string . '</label>';
        }

        return $lang_string;
    }

    /**
     * Load the translations for the given language.
     *
     * Note: Laravel handles language loading automatically.
     * This method sets the application locale.
     *
     * @origin Modules/Core/Helpers/trans_helper.php
     */
    public static function setLanguage(string $language): void
    {
        // Get default language from settings
        $default_lang = Setting::getValue('default_language') ?? 'en';
        $new_language = ($language == 'system' ? $default_lang : $language);

        // Set Laravel's application locale
        app()->setLocale($new_language);
    }

    /**
     * Returns all available languages.
     *
     * Note: Scans the resources/lang directory for available languages.
     *
     * @origin Modules/Core/Helpers/trans_helper.php
     */
    public static function getAvailableLanguages(): array
    {
        $lang_path = resource_path('lang');

        if ( ! is_dir($lang_path)) {
            return [];
        }

        $languages   = [];
        $directories = scandir($lang_path);

        foreach ($directories as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir($lang_path . '/' . $dir)) {
                $languages[] = $dir;
            }
        }

        sort($languages);

        return $languages;
    }
}
