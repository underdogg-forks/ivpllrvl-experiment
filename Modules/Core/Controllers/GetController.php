<?php

namespace Modules\Core\Controllers;

use Modules\Core\Controllers\UploadsService;

/*
 * InvoicePlane
 *
 * @author      InvoicePlane Developers & Contributors
 * @copyright   Copyright (c) 2012 - 2025 InvoicePlane.com
 * @license     https://invoiceplane.com/license.txt
 * @link        https://invoiceplane.com
 */
class GetController extends BaseController
{
    public $targetPath = UPLOADS_CFILES_FOLDER;

    // UPLOADS_FOLDER . 'customer_files/'
    public $ctype_default = 'application/octet-stream';

    public $content_types = [];

    /**
     * Initialize the controller and load MIME type mappings.
     *
     * Calls the parent constructor and sets the `$content_types` property using
     * the mappings provided by UploadsService.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->content_types = (new UploadsService())->content_types;
    }

    /**
     * @originalName showFiles
     *
     * @originalFile GetController.php
     */
    public function showFiles($url_key = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($url_key && ! $result = (new UploadsService())->getFiles($url_key)) {
            exit('{}');
        }
        echo json_encode($result);
        exit;
    }

    /**
     * @originalName getFile
     *
     * @originalFile GetController.php
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
     * Log a translated message, set the HTTP response code, output the translated message, and terminate execution.
     *
     * @param int    $httpCode        the HTTP status code to send in the response
     * @param string $messageKey      translation key used for the response message and for log lookup
     * @param string $dynamicLogValue optional additional context appended to the log entry
     */
    private function respondMessage(int $httpCode, string $messageKey, string $dynamicLogValue = ''): void
    {
        log_message('debug', 'guest/get: ' . trans($messageKey) . ': (status ' . $httpCode . ') ' . $dynamicLogValue);
        http_response_code($httpCode);
        _trans($messageKey);
        exit;
    }
}
