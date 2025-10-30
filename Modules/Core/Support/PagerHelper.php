<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Entities\Setting;


/**
 * PagerHelper
 * 
 * Static helper class converted from procedural functions.
 */
class PagerHelper
{
    /**
     * Returns a printable pagination.
     *
     * @param $base_url
     * @param $model
     */
    public static function pager(string $base_url, $model): string
    {
        // TODO: Migrate remaining CodeIgniter dependencies to Laravel
    
        $pager = '<div class="model-pager btn-group btn-group-sm">';
    
        if (($previous_page = $CI->{$model}->previous_offset) >= 0) {
            $pager .= '<a class="btn btn-default" href="' . $base_url . '/0" title="' . trans('first') . '"><i class="fa fa-fast-backward no-margin"></i></a>';
            $pager .= '<a class="btn btn-default" href="' . $base_url . '/' . $CI->{$model}->previous_offset . '" title="' . trans('prev') . '"><i class="fa fa-backward no-margin"></i></a>';
        } else {
            $pager .= '<a class="btn btn-default disabled" href="#" title="' . trans('first') . '"><i class="fa fa-fast-backward no-margin"></i></a>';
            $pager .= '<a class="btn btn-default disabled" href="#" title="' . trans('prev') . '"><i class="fa fa-backward no-margin"></i></a>';
        }
    
        if (($next_page = $CI->{$model}->next_offset) <= $CI->{$model}->last_offset) {
            $pager .= '<a class="btn btn-default" href="' . $base_url . '/' . $CI->{$model}->next_offset . '" title="' . trans('next') . '"><i class="fa fa-forward no-margin"></i></a>';
            $pager .= '<a class="btn btn-default" href="' . $base_url . '/' . $CI->{$model}->last_offset . '" title="' . trans('last') . '"><i class="fa fa-fast-forward no-margin"></i></a>';
        } else {
            $pager .= '<a class="btn btn-default disabled" href="#" title="' . trans('next') . '"><i class="fa fa-forward no-margin"></i></a>';
            $pager .= '<a class="btn btn-default disabled" href="#" title="' . trans('last') . '"><i class="fa fa-fast-forward no-margin"></i></a>';
        }
    
        return $pager . '</div>';
    }

}
