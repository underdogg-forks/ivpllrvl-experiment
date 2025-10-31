<?php

namespace Modules\Core\Support;

/**
 * PagerHelper.
 *
 * Static helper class converted from procedural functions.
 */
class PagerHelper
{
    /**
     * Returns a printable pagination.
     *
     * @deprecated This helper is deprecated and should be replaced with Laravel pagination.
     * Controllers should use Model::paginate() and views should use $items->links()
     *
     * @origin Modules/Core/Helpers/pager_helper.php
     * @param $base_url
     * @param $model
     * @return string
     */
    public static function pager(string $base_url, $model): string
    {
        // TODO: Replace with Laravel pagination
        // This method requires controller refactoring to use Laravel's paginate() method
        // Example:
        //   Controller: $items = Model::paginate(15);
        //   View: {{ $items->links() }}
        //
        // For now, return empty string to prevent CI dependency errors
        return '';
    }
}
