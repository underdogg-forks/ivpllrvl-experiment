<?php

namespace Modules\Core\Libraries;

use AllowDynamicProperties;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use SepaQr\Data;

#[AllowDynamicProperties]
class QrCode
{
    public $invoice;

    public $recipient;

    public $iban;

    public $bic;

    public $currencyCode;

    public $remittance_text;

    public function __construct(array $params)
    {
        $this->invoice         = $params['invoice'];
        $this->recipient       = $this->invoice->user_company ?: \Modules\Core\Models\Setting::getValue('qr_code_recipient');
        $this->iban            = $this->invoice->user_iban ?: \Modules\Core\Models\Setting::getValue('qr_code_iban');
        $this->bic             = $this->invoice->user_bic ?: \Modules\Core\Models\Setting::getValue('qr_code_bic');
        $this->currencyCode    = \Modules\Core\Models\Setting::getValue('currency_code');
        $this->remittance_text = \Modules\Core\Support\TemplateHelper::parse_template(
            $this->invoice,
            $this->invoice->user_remittance_text ?: \Modules\Core\Models\Setting::getValue('qr_code_remittance_text')
        );
    }

    public function paymentData()
    {
        return Data::create()
            ->setName($this->recipient)
            ->setIban($this->iban)
            ->setBic($this->bic)
            ->setCurrency($this->currencyCode)
            ->setRemittanceText($this->remittance_text)
            ->setAmount($this->invoice->invoice_balance);
    }

    public function generate(): string
    {
        return Builder::create()
            ->data($this->paymentData())
            ->errorCorrectionLevel(new ErrorCorrectionLevelMedium()) // required by EPC standard
            ->build()
            ->getDataUri();
    }
}
