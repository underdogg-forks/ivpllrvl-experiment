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
     * SECURITY: Removed dangerous file types (php, html, htm).
     */
    public array $content_types = [
        'pdf'  => 'application/pdf',
        'zip'  => 'application/zip',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
    ];

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

    /**
     * Get file names by URL key for attachments.
     *
     * @param string $urlKey
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttachmentsByUrlKey(string $urlKey)
    {
        return Upload::query()->select('file_name_new', 'file_name_original')
            ->where('url_key', $urlKey)
            ->get();
    }

    /**
     * Delete file metadata from database.
     *
     * @param string $urlKey
     * @param string $filename
     *
     * @return bool
     */
    public function deleteFile(string $urlKey, string $filename): bool
    {
        $safeFilename     = basename($filename);
        $expectedFilename = $urlKey . '_' . $safeFilename;

        return Upload::query()
            ->where('url_key', $urlKey)
            ->where('file_name_new', $expectedFilename)
            ->delete() > 0;
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): ?string
    {
        return Upload::class;
    }
}
