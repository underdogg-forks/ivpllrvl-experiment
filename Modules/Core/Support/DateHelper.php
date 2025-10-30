<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Services\LegacyBridge;

/**
 * Date Helper Class
 * 
 * Provides static methods for date formatting and manipulation.
 */
class DateHelper
{
    /**
     * Available date formats
     */
    public static function dateFormats(): array
    {
        return [
            'd/m/Y' => [
                'setting'    => 'd/m/Y',
                'datepicker' => 'dd/mm/yyyy',
            ],
            'd-m-Y' => [
                'setting'    => 'd-m-Y',
                'datepicker' => 'dd-mm-yyyy',
            ],
            'd-M-Y' => [
                'setting'    => 'd-M-Y',
                'datepicker' => 'dd-M-yyyy',
            ],
            'd.m.Y' => [
                'setting'    => 'd.m.Y',
                'datepicker' => 'dd.mm.yyyy',
            ],
            'j.n.Y' => [
                'setting'    => 'j.n.Y',
                'datepicker' => 'd.m.yyyy',
            ],
            'd M,Y' => [
                'setting'    => 'd M,Y',
                'datepicker' => 'dd M,yyyy',
            ],
            'm/d/Y' => [
                'setting'    => 'm/d/Y',
                'datepicker' => 'mm/dd/yyyy',
            ],
            'm-d-Y' => [
                'setting'    => 'm-d-Y',
                'datepicker' => 'mm-dd-yyyy',
            ],
            'm.d.Y' => [
                'setting'    => 'm.d.Y',
                'datepicker' => 'mm.dd.yyyy',
            ],
            'Y/m/d' => [
                'setting'    => 'Y/m/d',
                'datepicker' => 'yyyy/mm/dd',
            ],
            'Y-m-d' => [
                'setting'    => 'Y-m-d',
                'datepicker' => 'yyyy-mm-dd',
            ],
            'Y.m.d' => [
                'setting'    => 'Y.m.d',
                'datepicker' => 'yyyy.mm.dd',
            ],
        ];
    }

    /**
     * Convert MySQL date to user format
     */
    public static function dateFromMysql($date, bool $ignorePostCheck = false)
    {
        if ($date) {
            if (!$ignorePostCheck && isset($_POST['custom_date_format'])) {
                $date_format = $_POST['custom_date_format'];
            } else {
                $date_format = self::dateFormatSetting();
            }

            $date_object = \DateTime::createFromFormat('Y-m-d', $date);

            if ($date_object) {
                return $date_object->format($date_format);
            }
        }

        return '';
    }

    /**
     * Convert timestamp to user date format
     */
    public static function dateFromTimestamp($timestamp): string
    {
        $date_format = self::dateFormatSetting();
        return date($date_format, $timestamp);
    }

    /**
     * Convert user date to MySQL format
     */
    public static function dateToMysql($date)
    {
        if ($date) {
            if (isset($_POST['custom_date_format'])) {
                $date_format = $_POST['custom_date_format'];
            } else {
                $date_format = self::dateFormatSetting();
            }

            $date_object = \DateTime::createFromFormat($date_format, $date);

            if ($date_object) {
                return $date_object->format('Y-m-d');
            }
        }

        return '';
    }

    /**
     * Check if value is a valid date
     */
    public static function isDate($date): bool
    {
        if (isset($_POST['custom_date_format'])) {
            $date_format = $_POST['custom_date_format'];
        } else {
            $date_format = self::dateFormatSetting();
        }

        $date_object = \DateTime::createFromFormat($date_format, $date);

        return (bool) $date_object;
    }

    /**
     * Get date format setting
     */
    public static function dateFormatSetting()
    {
        $bridge = LegacyBridge::getInstance();
        $settings = $bridge->settings();
        
        if ($settings) {
            return $settings->setting('date_format');
        }
        
        return 'd/m/Y'; // Default fallback
    }

    /**
     * Get datepicker format
     */
    public static function dateFormatDatepicker()
    {
        $formats = self::dateFormats();
        $format  = self::dateFormatSetting();
        return $formats[$format]['datepicker'];
    }

    /**
     * Increment user date by interval
     */
    public static function incrementUserDate($date, string $increment): string
    {
        $date_object = \DateTime::createFromFormat(self::dateFormatSetting(), $date);

        if ($date_object) {
            $date_object->modify($increment);
            return $date_object->format(self::dateFormatSetting());
        }

        return '';
    }

    /**
     * Increment date by interval
     */
    public static function incrementDate($date, string $increment): string
    {
        $date_object = \DateTime::createFromFormat('Y-m-d', $date);

        if ($date_object) {
            $date_object->modify($increment);
            return $date_object->format('Y-m-d');
        }

        return '';
    }
}
