<?php

namespace Modules\Core\Controllers;

use AllowDynamicProperties;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\UploadService;

#[AllowDynamicProperties]
class UploadController extends AdminController
{
    /**
     * @var string
     */
    public string $targetPath;

    /**
     * @var string
     */
    public string $ctype_default = 'application/octet-stream';

    /**
     * @var array<string, string>
     */
    public array $content_types;

    /**
     * UploadController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->targetPath    = config('filesystems.cfiles_folder');
        $this->content_types = (new UploadService())->content_types;
    }

    /**
     * Handle file upload for a customer.
     *
     * @param Request $request
     * @param int     $customerId
     * @param string  $url_key
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(Request $request, int $customerId, string $url_key): \Illuminate\Http\JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->file('file');
        if ( ! $file) {
            return response()->json(['message' => 'upload_error_no_file'], 400);
        }
        $filename = $this->sanitizeFileName($file->getClientOriginalName());
        $filePath = $this->getTargetFilePath($url_key, $filename);
        if (file_exists($filePath)) {
            return response()->json(['message' => 'upload_error_duplicate_file', 'filename' => $filename], 409);
        }
        $this->validateMimeType($file->getMimeType());
        $file->move(dirname($filePath), $filename);
        $this->saveFileMetadata($customerId, $url_key, $filename);

        return response()->json(['message' => 'upload_file_uploaded_successfully', 'filename' => $filename], 200);
    }

    /**
     * Create a directory if it does not exist.
     *
     * @param string $path
     * @param int    $chmod
     *
     * @return bool
     */
    public function createDir(string $path, int $chmod = 0755): bool
    {
        if ( ! is_dir($path) && ! is_link($path)) {
            return mkdir($path, $chmod);
        }

        return true;
    }

    /**
     * @originalName showFiles
     *
     * @originalFile UploadController.php
     */
    public function showFiles($url_key = null)
    {
        $result = $url_key ? (new UploadService())->getFiles($url_key) : [];

        return response()->json($result ?? []);
    }

    /**
     * @originalName deleteFile
     *
     * @originalFile UploadController.php
     */
    public function deleteFile(Request $request, string $url_key)
    {
        $filename  = urldecode($request->input('name'));
        $finalPath = $this->targetPath . $url_key . '_' . $filename;
        if (realpath($this->targetPath) === mb_substr(realpath($finalPath), 0, mb_strlen(realpath($this->targetPath))) && ( ! file_exists($finalPath) || @unlink($finalPath))) {
            (new UploadService())->deleteFile($url_key, $filename);

            return response()->json(['message' => 'upload_file_deleted_successfully', 'filename' => $filename], 200);
        }
        $ref = $request->headers->get('referer') ? ', Referer:' . $request->headers->get('referer') : '';

        return response()->json(['message' => 'upload_error_file_delete', 'filename' => $finalPath . $ref], 410);
    }

    /**
     * @originalName getFile
     *
     * @originalFile UploadController.php
     */
    public function getFile($filename): void
    {
        $filename = urldecode($filename);
        if ( ! file_exists($this->targetPath . $filename)) {
            $ref = isset($_SERVER['HTTP_REFERER']) ? ', Referer:' . $_SERVER['HTTP_REFERER'] : '';
            $this->respondMessage(404, 'upload_error_file_not_found', $this->targetPath . $filename . $ref);
        }
        $path_parts = pathinfo($this->targetPath . $filename);
        $file_ext   = mb_strtolower($path_parts['extension'] ?? '');
        $ctype      = $this->content_types[$file_ext] ?? $this->ctype_default;
        $file_size  = filesize($this->targetPath . $filename);
        header('Expires: -1');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: ' . $ctype);
        header('Content-Length: ' . $file_size);
        readfile($this->targetPath . $filename);
    }

    /**
     * @originalName sanitizeFileName
     *
     * @originalFile UploadController.php
     */
    private function sanitizeFileName(string $filename): string
    {
        // Clean filename (same in dropzone script)
        return preg_replace("/[^\\p{L}\\p{N}\\s\\-_'’.]/u", '', mb_trim($filename));
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
     * @originalName saveFileMetadata
     *
     * @originalFile UploadController.php
     */
    private function saveFileMetadata(int $customerId, string $url_key, string $filename): void
    {
        $data = ['client_id' => $customerId, 'url_key' => $url_key, 'file_name_original' => $filename, 'file_name_new' => $url_key . '_' . $filename];
        if ( ! (new UploadService())->create($data)) {
            $this->respondMessage(500, 'upload_error_database', $filename);
        }
    }

    /**
     * @originalName moveUploadedFile
     *
     * @originalFile UploadController.php
     */
    private function moveUploadedFile(string $tempFile, string $filePath, string $filename): void
    {
        // Create the target dir (if unexist)
        $this->createDir($this->targetPath);
        // Checks to ensure that the target dir is writable
        if ( ! is_writable($this->targetPath)) {
            $this->respondMessage(410, 'upload_error_folder_not_writable', $this->targetPath);
        } elseif ( ! move_uploaded_file($tempFile, $filePath)) {
            $this->respondMessage(400, 'upload_error_invalid_move_uploaded_file', $filename);
        }
    }

    /**
     * @originalName respondMessage
     *
     * @originalFile UploadController.php
     */
    private function respondMessage(int $httpCode, string $messageKey, string $dynamicLogValue = ''): void
    {
        Log::debug(trans($messageKey) . ': (status ' . $httpCode . ') ' . $dynamicLogValue);
        http_response_code($httpCode);
        _trans($messageKey);
        if ($httpCode == 410) {
            echo PHP_EOL . PHP_EOL . '"' . basename(config('filesystems.folder')) . DIRECTORY_SEPARATOR . basename($this->targetPath) . '"';
        }
        exit;
    }
}
