<?php

namespace Modules\Core\Models;

/**
 * Version Model.
 *
 * Eloquent model for tracking database version updates
 * Migrated from CodeIgniter Mdl_Versions model
 *
 * @property int    $version_id
 * @property string $version_date_applied
 * @property string $version_file
 * @property int    $version_sql_errors
 */
class Version extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_versions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'version_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'version_date_applied',
        'version_file',
        'version_sql_errors',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'version_id'         => 'integer',
        'version_sql_errors' => 'integer',
    ];

    /**
     * Get the current version from the database.
     *
     * @return string
     */
    public static function getCurrentVersion(): string
    {
        $version = static::orderBy('version_date_applied', 'desc')
            ->orderBy('version_file', 'desc')
            ->first();

        if ( ! $version) {
            return '1.0.0';
        }

        $versionFile   = $version->version_file;
        $underscorePos = mb_strpos($versionFile, '_');

        if ($underscorePos !== false) {
            $versionStr = mb_substr($versionFile, $underscorePos + 1);

            return str_replace('.sql', '', $versionStr);
        }

        return '1.0.0';
    }
}
