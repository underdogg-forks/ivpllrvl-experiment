<?php

namespace Modules\Core\Support;

use DateTime;
use Modules\Core\Models\Setting;

/**
 * Date Helper Class.
 *
 * Provides static methods for date formatting and manipulation.
 */
class DateHelper
{
    /**
     * Available date formats.
     *
     * @origin Modules/Core/Helpers/date_helper.php
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
     * Convert MySQL date to user format.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function dateFromMysql($date, bool $ignorePostCheck = false)
    {
        if ($date) {
            // SECURITY FIX: Use Request facade instead of direct $_POST access
            if ( ! $ignorePostCheck && request()->has('custom_date_format')) {
                $date_format = request()->input('custom_date_format');
            } else {
                $date_format = self::dateFormatSetting();
            }

            $date_object = DateTime::createFromFormat('Y-m-d', $date);

            if ($date_object) {
                return $date_object->format($date_format);
            }
        }

        return '';
    }

    /**
     * Convert timestamp to user date format.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function dateFromTimestamp($timestamp): string
    {
        $date_format = self::dateFormatSetting();

        return date($date_format, $timestamp);
    }

    /**
     * Convert user date to MySQL format.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function dateToMysql($date)
    {
        if ($date) {
            // SECURITY FIX: Use Request facade instead of direct $_POST access
            if (request()->has('custom_date_format')) {
                $date_format = request()->input('custom_date_format');
            } else {
                $date_format = self::dateFormatSetting();
            }

            $date_object = DateTime::createFromFormat($date_format, $date);

            if ($date_object) {
                return $date_object->format('Y-m-d');
            }
        }

        return '';
    }

    /**
     * Check if value is a valid date.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function isDate($date): bool
    {
        // SECURITY FIX: Use Request facade instead of direct $_POST access
        if (request()->has('custom_date_format')) {
            $date_format = request()->input('custom_date_format');
        } else {
            $date_format = self::dateFormatSetting();
        }

        $date_object = DateTime::createFromFormat($date_format, $date);

        return (bool) $date_object;
    }

    /**
     * Get date format setting.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function dateFormatSetting()
    {
        return Setting::getValue('date_format') ?? 'd/m/Y';
    }

    /**
     * Get datepicker format.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function dateFormatDatepicker()
    {
        $formats = self::dateFormats();
        $format  = self::dateFormatSetting();

        return $formats[$format]['datepicker'];
    }

    /**
     * Increment user date by interval.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function incrementUserDate($date, string $increment): string
    {
        $date_object = DateTime::createFromFormat(self::dateFormatSetting(), $date);

        if ($date_object) {
            $date_object->modify($increment);

            return $date_object->format(self::dateFormatSetting());
        }

        return '';
    }

    /**
     * Increment date by interval.
     *
     * @origin Modules/Core/Helpers/date_helper.php
     */
    public static function incrementDate($date, string $increment): string
    {
        $date_object = DateTime::createFromFormat('Y-m-d', $date);

        if ($date_object) {
            $date_object->modify($increment);

            return $date_object->format('Y-m-d');
        }

        return '';
    }
}
