<?php

namespace Modules\Invoices\Services;

class TemplateService
{
    public function getInvoiceTemplates(string $type = 'pdf'): array
    {
        $path = $type === 'pdf'
            ? APPPATH . '/views/invoice_templates/pdf'
            : APPPATH . '/views/invoice_templates/public';

        return $this->removeExtension($this->getTemplatesFromPath($path));
    }

    public function getQuoteTemplates(string $type = 'pdf'): array
    {
        $path = $type === 'pdf'
            ? APPPATH . '/views/quote_templates/pdf'
            : APPPATH . '/views/quote_templates/public';

        return $this->removeExtension($this->getTemplatesFromPath($path));
    }

    private function getTemplatesFromPath(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        return array_values(array_filter(scandir($path) ?: [], function ($file) {
            return ! in_array($file, ['.', '..'], true);
        }));
    }

    private function removeExtension(array $files): array
    {
        return array_map(static function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);
    }
}
