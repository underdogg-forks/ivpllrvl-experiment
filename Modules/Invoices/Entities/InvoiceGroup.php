<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

/**
 * InvoiceGroup Model
 * 
 * Eloquent model for managing ip_invoice_groups
 * Migrated from CodeIgniter model
 */
class InvoiceGroup extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_groups';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_group_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_group_name',
        'invoice_group_identifier_format',
        'invoice_group_next_id',
        'invoice_group_left_pad',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_group_id' => 'integer',
        'invoice_group_next_id' => 'integer',
        'invoice_group_left_pad' => 'integer',
    ];

    /**
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('invoice_group_name');
    }

    /**
     * Get invoices that belong to this group
     */
    public function invoices()
    {
        return $this->hasMany('Modules\Invoices\Entities\Invoice', 'invoice_group_id', 'invoice_group_id');
    }

    /**
     * Generate invoice number for this group
     *
     * @param bool $set_next Whether to increment next_id
     * @return string
     */
    public function generateInvoiceNumber($set_next = true)
    {
        $invoice_identifier = $this->parseIdentifierFormat(
            $this->invoice_group_identifier_format,
            $this->invoice_group_next_id,
            $this->invoice_group_left_pad
        );

        if ($set_next) {
            $this->setNextInvoiceNumber();
        }

        return $invoice_identifier;
    }

    /**
     * Increment the next invoice number
     */
    public function setNextInvoiceNumber()
    {
        $this->increment('invoice_group_next_id');
    }

    /**
     * Parse identifier format with template variables
     *
     * @param string $identifier_format
     * @param string $next_id
     * @param int $left_pad
     * @return string
     */
    private function parseIdentifierFormat($identifier_format, $next_id, $left_pad)
    {
        if (preg_match_all('/{{{([^{|}]*)}}}/', $identifier_format, $template_vars)) {
            foreach ($template_vars[1] as $var) {
                switch ($var) {
                    case 'year':
                        $replace = date('Y');
                        break;
                    case 'yy':
                        $replace = date('y');
                        break;
                    case 'month':
                        $replace = date('m');
                        break;
                    case 'day':
                        $replace = date('d');
                        break;
                    case 'id':
                        $replace = mb_str_pad($next_id, $left_pad, '0', STR_PAD_LEFT);
                        break;
                    default:
                        $replace = '';
                }

                $identifier_format = str_replace('{{{' . $var . '}}}', $replace, $identifier_format);
            }
        }

        return $identifier_format;
    }

    /**
     * Get validation rules for invoice groups.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'invoice_group_name' => 'required|string|max:255',
            'invoice_group_identifier_format' => 'required|string',
            'invoice_group_next_id' => 'required|integer|min:1',
            'invoice_group_left_pad' => 'required|integer|min:0',
        ];
    }
}
