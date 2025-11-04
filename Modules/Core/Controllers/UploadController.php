<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\UploadService;

/**
 * UploadController
 *
 * Handles file upload operations
 *
 * @legacy-file application/modules/uploads/controllers/Uploads.php
 */
class UploadController
{
    protected UploadService $uploadService;
    protected string $targetPath;
    protected string $ctype_default = 'application/octet-stream';
    protected array $content_types;

    /**
     * Initialize the UploadController with dependency injection.
     *
     * @param UploadService $uploadService
     */
    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
        $this->targetPath = config('filesystems.cfiles_folder');
        $this->content_types = $uploadService->content_types;
    }

    /**
     * Handle file upload for a customer.
     *
     * @param Request $request
     * @param int $customerId
     * @param string $url_key
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @legacy-function uploadFile
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    public function uploadFile(Request $request, int $customerId, string $url_key): \Illuminate\Http\JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->file('file');
        if (!$file) {
            return response()->json(['message' => trans('upload_error_no_file')], 400);
        }

        // SECURITY FIX: Validate file size (10MB max)
        $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($file->getSize() > $maxFileSize) {
            return response()->json(['message' => trans('upload_error_file_too_large')], 413);
        }

        // SECURITY FIX: Improved filename sanitization
        $filename = $this->sanitizeFileName($file->getClientOriginalName());
        
        // SECURITY FIX: Validate file extension against whitelist
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = array_keys($this->content_types);
        
        if (!in_array($extension, $allowedExtensions, true)) {
            return response()->json(['message' => trans('upload_error_unsupported_file_type'), 'extension' => $extension], 415);
        }

        // SECURITY FIX: Validate url_key
        $safeUrlKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $url_key);
        
        // Build safe file path
        $safeFilename = $safeUrlKey . '_' . $filename;
        $filePath = $this->targetPath . $safeFilename;

        if (file_exists($filePath)) {
            return response()->json(['message' => trans('upload_error_duplicate_file'), 'filename' => $filename], 409);
        }

        // SECURITY FIX: Validate MIME type AND extension
        $this->validateMimeType($file->getMimeType());
        
        // Move file to target directory
        $file->move($this->targetPath, $safeFilename);
        
        // Save metadata
        $this->saveFileMetadata($customerId, $url_key, $filename);

        return response()->json(['message' => trans('upload_file_uploaded_successfully'), 'filename' => $filename], 200);
    }

    /**
     * Create a directory if it does not exist.
     *
     * @param string $path
     * @param int $chmod
     *
     * @return bool
     *
     * @legacy-function createDir
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    public function createDir(string $path, int $chmod = 0755): bool
    {
        if (!is_dir($path) && !is_link($path)) {
            return mkdir($path, $chmod);
        }

        return true;
    }

    /**
     * Show files by URL key (AJAX endpoint).
     *
     * @param string|null $url_key
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @legacy-function showFiles
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    public function showFiles(?string $url_key = null): \Illuminate\Http\JsonResponse
    {
        $result = $url_key ? $this->uploadService->getFiles($url_key) : [];

        return response()->json($result ?? []);
    }

    /**
     * Delete file by URL key and filename.
     *
     * @param Request $request
     * @param string $url_key
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @legacy-function deleteFile
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    public function deleteFile(Request $request, string $url_key): \Illuminate\Http\JsonResponse
    {
        $filename = urldecode($request->input('name'));

        // SECURITY FIX: Validate url_key and filename to prevent path traversal
        $safeFilename = basename($filename);
        $safeUrlKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $url_key);

        // Build expected filename with url_key prefix
        $expectedFilename = $safeUrlKey . '_' . $safeFilename;
        $candidatePath = $this->targetPath . $expectedFilename;
        $resolvedPath = realpath($candidatePath);
        $allowedPath = realpath($this->targetPath);

        // Verify path is within allowed directory
        if ($resolvedPath === false || strpos($resolvedPath, $allowedPath) !== 0) {
            return response()->json(['message' => trans('upload_error_file_delete'), 'filename' => $safeFilename], 410);
        }

        // Attempt to delete the file
        if (file_exists($resolvedPath) && unlink($resolvedPath)) {
            $this->uploadService->deleteFile($url_key, $filename);
            return response()->json(['message' => trans('upload_file_deleted_successfully'), 'filename' => $safeFilename], 200);
        }

        return response()->json(['message' => trans('upload_error_file_delete'), 'filename' => $safeFilename], 410);
    }

    /**
     * Download file by filename.
     *
     * @param string $filename
     *
     * @return void
     *
     * @legacy-function getFile
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    public function getFile(string $filename): void
    {
        $filename = urldecode($filename);

        // SECURITY FIX: Prevent path traversal by using only the basename
        $safeFilename = basename($filename);

        // Build candidate path and resolve to canonical path
        $candidatePath = $this->targetPath . $safeFilename;
        $resolvedPath = realpath($candidatePath);
        $allowedPath = realpath($this->targetPath);

        // Verify resolved path exists and is within the allowed directory
        if ($resolvedPath === false || strpos($resolvedPath, $allowedPath) !== 0) {
            $this->respondMessage(404, 'upload_error_file_not_found', '');
        }

        if (!file_exists($resolvedPath)) {
            $this->respondMessage(404, 'upload_error_file_not_found', '');
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
     * @originalName sanitizeFileName
     *
     * @originalFile UploadController.php
     */
        private function sanitizeFileName(string $filename): string
    {
        // SECURITY FIX: Improved sanitization
        // 1. Get the file extension first
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // 2. Clean the basename - only allow alphanumeric, dash, underscore
        $cleanBasename = preg_replace("/[^a-zA-Z0-9_-]/", '_', $basename);
        
        // 3. Limit length to prevent long filename attacks
        $cleanBasename = mb_substr($cleanBasename, 0, 200);
        
        // 4. Remove consecutive underscores/dashes
        $cleanBasename = preg_replace('/_+/', '_', $cleanBasename);
        $cleanBasename = preg_replace('/-+/', '-', $cleanBasename);
        
        // 5. Trim leading/trailing underscores/dashes
        $cleanBasename = trim($cleanBasename, '_-');
        
        // 6. Ensure we have a valid basename
        if (empty($cleanBasename)) {
            $cleanBasename = 'file_' . time();
        }
        
        // 7. Reconstruct filename with cleaned extension
        $cleanExtension = preg_replace("/[^a-zA-Z0-9]/", '', $extension);
        
        return $cleanBasename . '.' . $cleanExtension;
    }\\p{N}\\s\\-_'’.]/u", '', mb_trim($filename));
    }

    /**
     * @originalName getTargetFilePath
     *
     * @originalFile UploadController.php
     */
    private function getTargetFilePath(string $url_key, string $filename): string
    {
        return $this->targetPath . $url_key . '_' . $filename;
    }

    /**
     * @originalName validateMimeType
     *
     * @originalFile UploadController.php
     */
    private function validateMimeType(string $mimeType): void
    {
        $allowedTypes = array_values($this->content_types);
        if ( ! in_array($mimeType, $allowedTypes, true)) {
            $this->respondMessage(415, 'upload_error_unsupported_file_type', $mimeType);
        }
    }

    /**
     * Save file metadata.
     *
     * @param int $customerId
     * @param string $url_key
     * @param string $filename
     *
     * @return void
     *
     * @legacy-function saveFileMetadata
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    private function saveFileMetadata(int $customerId, string $url_key, string $filename): void
    {
        $data = [
            'client_id' => $customerId,
            'url_key' => $url_key,
            'file_name_original' => $filename,
            'file_name_new' => $url_key . '_' . $filename,
        ];

        if (!$this->uploadService->create($data)) {
            $this->respondMessage(500, 'upload_error_database', $filename);
        }
    }

    /**
     * Move uploaded file.
     *
     * @param string $tempFile
     * @param string $filePath
     * @param string $filename
     *
     * @return void
     *
     * @legacy-function moveUploadedFile
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    private function moveUploadedFile(string $tempFile, string $filePath, string $filename): void
    {
        // Create the target dir (if unexist)
        $this->createDir($this->targetPath);

        // Checks to ensure that the target dir is writable
        if (!is_writable($this->targetPath)) {
            $this->respondMessage(410, 'upload_error_folder_not_writable', $this->targetPath);
        } elseif (!move_uploaded_file($tempFile, $filePath)) {
            $this->respondMessage(400, 'upload_error_invalid_move_uploaded_file', $filename);
        }
    }

    /**
     * Send error response and exit.
     *
     * @param int $httpCode
     * @param string $messageKey
     * @param string $dynamicLogValue
     *
     * @return void
     *
     * @legacy-function respondMessage
     * @legacy-file application/modules/uploads/controllers/Uploads.php
     */
    private function respondMessage(int $httpCode, string $messageKey, string $dynamicLogValue = ''): void
    {
        Log::debug(trans($messageKey) . ': (status ' . $httpCode . ') ' . $dynamicLogValue);
        http_response_code($httpCode);
        echo trans($messageKey);

        if ($httpCode == 410) {
            echo PHP_EOL . PHP_EOL . '"' . basename(config('filesystems.folder')) . DIRECTORY_SEPARATOR . basename($this->targetPath) . '"';
        }

        exit;
    }
}
