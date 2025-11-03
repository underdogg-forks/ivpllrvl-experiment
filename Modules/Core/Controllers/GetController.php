<?php

namespace Modules\Core\Controllers;

use Modules\Core\Services\UploadService;

/**
 * GetController
 *
 * Handles file download and retrieval operations
 *
 * @legacy-file application/modules/guest/controllers/Get.php
 *
 * @author InvoicePlane Developers & Contributors
 * @copyright Copyright (c) 2012 - 2025 InvoicePlane.com
 * @license https://invoiceplane.com/license.txt
 * @link https://invoiceplane.com
 */
class GetController
{
    protected UploadService $uploadService;
    protected string $targetPath;
    protected string $ctype_default = 'application/octet-stream';
    protected array $content_types = [];

    /**
     * Initialize the GetController with dependency injection.
     *
     * @param UploadService $uploadService
     */
    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
        $this->targetPath = defined('UPLOADS_CFILES_FOLDER') ? UPLOADS_CFILES_FOLDER : './uploads/customer_files/';
        $this->content_types = $uploadService->content_types;
    }

    /**
     * Show files by URL key (AJAX endpoint).
     *
     * @param string|null $urlKey URL key
     *
     * @return void Outputs JSON response
     *
     * @legacy-function showFiles
     * @legacy-file application/modules/guest/controllers/Get.php
     */
    public function showFiles(?string $urlKey = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $result = $this->uploadService->getFiles($urlKey);
        
        if (!$urlKey || !$result) {
            echo '{}';
            exit;
        }
        
        echo json_encode($result);
        exit;
    }

    /**
     * Download a file by filename.
     *
     * @param string $filename Filename to download
     *
     * @return void
     *
     * @legacy-function getFile
     * @legacy-file application/modules/guest/controllers/Get.php
     */
    public function getFile(string $filename): void
    {
        $filename = urldecode($filename);
        
        // Prevent path traversal: use only the basename (filename without directory components)
        $safeFilename = basename($filename);
        
        // Build candidate path and get canonical path
        $candidatePath = $this->targetPath . $safeFilename;
        $resolvedPath = realpath($candidatePath);
        
        // Verify resolved path exists and is within the allowed directory
        if ($resolvedPath === false || strpos($resolvedPath, realpath($this->targetPath)) !== 0) {
            $ref = isset($_SERVER['HTTP_REFERER']) ? ', Referer:' . $_SERVER['HTTP_REFERER'] : '';
            $this->respondMessage(404, 'upload_error_file_not_found', $candidatePath . $ref);
        }
        
        if (!file_exists($resolvedPath)) {
            $ref = isset($_SERVER['HTTP_REFERER']) ? ', Referer:' . $_SERVER['HTTP_REFERER'] : '';
            $this->respondMessage(404, 'upload_error_file_not_found', $resolvedPath . $ref);
        }
        
        $path_parts = pathinfo($resolvedPath);
        $file_ext = mb_strtolower($path_parts['extension'] ?? '');
        $ctype = $this->content_types[$file_ext] ?? $this->ctype_default;
        $file_size = filesize($resolvedPath);
        
        header('Expires: -1');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Content-Type: ' . $ctype);
        header('Content-Length: ' . $file_size);
        
        readfile($resolvedPath);
    }

    /**
     * Send an error response and exit.
     *
     * @param int $httpCode HTTP status code
     * @param string $messageKey Translation key for message
     * @param string $dynamicLogValue Additional context for logging
     *
     * @return void
     *
     * @legacy-function respondMessage
     * @legacy-file application/modules/guest/controllers/Get.php
     */
    private function respondMessage(int $httpCode, string $messageKey, string $dynamicLogValue = ''): void
    {
        log_message('debug', 'guest/get: ' . trans($messageKey) . ': (status ' . $httpCode . ') ' . $dynamicLogValue);
        http_response_code($httpCode);
        echo trans($messageKey);
        exit;
    }
}
