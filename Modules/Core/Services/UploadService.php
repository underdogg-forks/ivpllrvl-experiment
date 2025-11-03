<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Upload;

/**
 * UploadService.
 *
 * Service class for managing file uploads
 *
 * @legacy-file application/modules/uploads/models/Mdl_uploads.php (inferred)
 */
class UploadService extends BaseService
{
    /**
     * MIME type mappings for common file extensions.
     */
    public array $content_types = [
        'pdf' => 'application/pdf',
        'exe' => 'application/octet-stream',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/x-wav',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/plain',
    ];

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): ?string
    {
        return Upload::class;
    }

    /**
     * Get files by URL key.
     *
     * @param string $urlKey URL key
     *
     * @return array|null File data
     *
     * @legacy-function getFiles
     */
    public function getFiles(string $urlKey): ?array
    {
        // TODO: Implement get files logic
        $upload = Upload::query()
            ->where('url_key', $urlKey)
            ->first();

        return $upload ? $upload->toArray() : null;
    }
}
