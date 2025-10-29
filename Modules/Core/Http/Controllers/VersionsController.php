<?php

namespace Modules\Core\Http\Controllers;

use Modules\Core\Entities\Version;

/**
 * VersionsController
 * 
 * Displays version history and update information
 * Migrated from CodeIgniter Versions controller
 */
class VersionsController
{
    /**
     * Display version history
     *
     * @param int $page Page number for pagination
     */
    public function index(int $page = 0)
    {
        $perPage = 15;
        
        $versions = Version::orderBy('version_date_applied', 'desc')
            ->orderBy('version_file', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return view('core::versions', compact('versions'));
    }
}

