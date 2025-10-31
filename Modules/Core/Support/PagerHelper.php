<?php

namespace Modules\Core\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * PagerHelper.
 *
 * Static helper class converted from procedural functions.
 * Provides a bridge between legacy pager() calls and Laravel pagination.
 */
class PagerHelper
{
    /**
     * Returns a printable pagination with Laravel pagination bridge.
     *
     * This method provides backward compatibility for legacy pager() calls
     * while migrating to Laravel's pagination system.
     *
     * Migration Guide:
     * ----------------
     * Controllers should be updated to use Model::paginate() directly:
     *   Before: $items = Model::all(); pager($url, 'mdl_items');
     *   After:  $items = Model::paginate(15); $items->links();
     *
     * Behavior:
     * ---------
     * 1. If $model is LengthAwarePaginator or Paginator: returns ->links() HTML
     * 2. If $model is Eloquent\Builder or Query\Builder: calls ->paginate($perPage) and returns ->links()
     * 3. For legacy arrays/collections: returns empty string (fallback)
     *
     * @origin Modules/Core/Helpers/pager_helper.php
     *
     * @param string $base_url Base URL for pagination (legacy parameter, may not be used)
     * @param mixed  $model    Paginator instance, Builder instance, or legacy collection
     * @param int    $perPage  Items per page when auto-paginating builders (default: 15)
     *
     * @return string HTML pagination links or empty string
     */
    public static function pager(string $base_url, $model, int $perPage = 15): string
    {
        // Case 1: Already a paginator instance - return links directly
        if ($model instanceof LengthAwarePaginator || $model instanceof Paginator) {
            try {
                return $model->links()->toHtml();
            } catch (\Throwable $e) {
                // If view factory isn't set up, return empty string
                // This can happen in test/CLI contexts
                return '';
            }
        }

        // Case 2: Eloquent or Query Builder - paginate and return links
        if ($model instanceof EloquentBuilder || $model instanceof QueryBuilder) {
            try {
                $paginated = $model->paginate($perPage);

                return $paginated->links()->toHtml();
            } catch (\Throwable $e) {
                // If pagination or view rendering fails, return empty string
                return '';
            }
        }

        // Case 3: Legacy arrays, collections, or unsupported types - return empty string
        // This maintains backward compatibility with views that aren't yet updated
        return '';
    }
}
