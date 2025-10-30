<?php

declare(strict_types=1);

namespace Modules\Core\Support;

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
        $CI = &get_instance();
        $lang_string = $CI->lang->line($line);

        // Fall back to default language if the current language has no translated string
        if (empty($lang_string)) {
            // Save the current application language
            $current_language = $CI->session->userdata('user_language');

            if (empty($current_language) || $current_language == 'system') {
                $current_language = get_setting('default_language') ?? 'english';
            }

            // Load the default language and translate the string
            self::setLanguage('english');
            $lang_string = $CI->lang->line($line);

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
        // Clear the current loaded language
        $CI = &get_instance();
        $CI->lang->is_loaded = [];
        $CI->lang->language = [];

        // Load system language if no custom language is set
        $default_lang = isset($CI->mdl_settings) ? $CI->mdl_settings->setting('default_language') : 'english';
        $new_language = ($language == 'system' ? $default_lang : $language);

        $app_dir = $CI->config->_config_paths[0];
        $lang_dir = $app_dir . DIRECTORY_SEPARATOR . 'language';

        // Set the new language
        $CI->lang->load('ip', $new_language);
        $CI->lang->load('form_validation', $new_language);
        if (file_exists($lang_dir . DIRECTORY_SEPARATOR . $default_lang . DIRECTORY_SEPARATOR . 'custom_lang.php')) {
            $CI->lang->load('custom', $new_language);
        }

        $CI->lang->load('gateway', $new_language);
    }

    /**
     * Returns all available languages.
     */
    public static function getAvailableLanguages(): array
    {
        $CI = &get_instance();
        $CI->load->helper('directory');

        $languages = directory_map(APPPATH . 'language', true);
        sort($languages);
        $counter = count($languages);

        for ($i = 0; $i < $counter; $i++) {
            $languages[$i] = str_replace(['\\', '/'], '', $languages[$i]);
        }

        return $languages;
    }
}
