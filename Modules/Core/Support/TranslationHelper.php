<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Services\LegacyBridge;

/**
 * Translation Helper Class
 * 
 * Provides static methods for language translation and management.
 */
class TranslationHelper
{
    /**
     * Output a language string, supports language fallback if a string wasn't found.
     */
    public static function trans($line, ?string $id = '', $default = null): string
    {
        $bridge = LegacyBridge::getInstance();
        $lang = $bridge->lang();
        $session = $bridge->session();
        
        if (!$lang) {
            return $default ?? $line;
        }
        
        $lang_string = $lang->line($line);

        // Fall back to default language if the current language has no translated string
        if (empty($lang_string) && $session) {
            // Save the current application language
            $current_language = $session->userdata('user_language');

            if (empty($current_language) || $current_language == 'system') {
                $current_language = get_setting('default_language') ?? 'english';
            }

            // Load the default language and translate the string
            self::setLanguage('english');
            $lang_string = $lang->line($line);

            // Restore the application language to its previous setting
            self::setLanguage($current_language);
        }

        // Fall back to the $line value if the default language has no translation either
        if (empty($lang_string)) {
            $lang_string = $default != null ? $default : $line;
        }

        if ($id != '') {
            $lang_string = '<label for="' . $id . '">' . $lang_string . '</label>';
        }

        return $lang_string;
    }

    /**
     * Load the translations for the given language.
     */
    public static function setLanguage(string $language): void
    {
        $bridge = LegacyBridge::getInstance();
        $lang = $bridge->lang();
        $config = $bridge->config();
        
        if (!$lang || !$config) {
            return;
        }
        
        // Clear the current loaded language
        $lang->is_loaded = [];
        $lang->language = [];

        // Load system language if no custom language is set
        $settings = $bridge->settings();
        $default_lang = $settings ? $settings->setting('default_language', 'english') : 'english';
        $new_language = ($language == 'system' ? $default_lang : $language);

        $app_dir = $config->_config_paths[0];
        $lang_dir = $app_dir . DIRECTORY_SEPARATOR . 'language';

        // Set the new language
        $lang->load('ip', $new_language);
        $lang->load('form_validation', $new_language);
        if (file_exists($lang_dir . DIRECTORY_SEPARATOR . $default_lang . DIRECTORY_SEPARATOR . 'custom_lang.php')) {
            $lang->load('custom', $new_language);
        }

        $lang->load('gateway', $new_language);
    }

    /**
     * Returns all available languages.
     */
    public static function getAvailableLanguages(): array
    {
        $bridge = LegacyBridge::getInstance();
        
        if (!$bridge->isAvailable()) {
            return [];
        }
        
        $bridge->loadHelper('directory');

        $languages = directory_map(APPPATH . 'language', true);
        sort($languages);
        $counter = count($languages);

        for ($i = 0; $i < $counter; $i++) {
            $languages[$i] = str_replace(['\\', '/'], '', $languages[$i]);
        }

        return $languages;
    }
}
