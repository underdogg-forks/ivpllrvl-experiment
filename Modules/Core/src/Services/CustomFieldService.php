<?php

namespace Modules\Core\Services;

use Modules\Core\Models\CustomField;

/**
 * CustomFieldService.
 *
 * Service class for managing custom field business logic
 */
class CustomFieldService extends BaseService
{
    /**
     * @return string[]
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function custom_types()
     */
    public static function custom_types()
    {
/*
        $CI = &get_instance();
        $CI->load->model('custom_values/custom_value');

        return Mdl_Custom_Values::custom_types();
*/
    }

    /**
     * @param $table
     *
     * @return $this
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_by_table()
     */
    public function get_by_table($table)
    {
/*
        $this->where('custom_field_table', $table);

        return $this->get()->result();
*/
    }

    /**
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function save()
     */
    public function save($id = null, $db_array = null)
    {
/*
        if ($id) {
            // Get the original record before saving
            $original_record = $this->get_by_id($id);
        }

        // Create the record
        $db_array = ($db_array) ? $db_array : $this->db_array();

        // Save the record to ip_custom_fields
        $id = parent::save($id, $db_array);

        return $id;
*/
    }

    /**
     * @param $table_name
     *
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_positions()
     */
    public function get_positions($table_name = false)
    {
/*
        $this->load->model(
            [
                'custom_fields/mdl_client_custom',
                'custom_fields/mdl_invoice_custom',
                'custom_fields/mdl_payment_custom',
                'custom_fields/mdl_quote_custom',
                'custom_fields/mdl_user_custom',
            ]
        );

        $p = $table_name ? 'ip_' : '';
        $s = $table_name ? '_custom' : '';

        $positions = [
            $p . 'client' . $s  => Mdl_client_custom::$positions,
            $p . 'invoice' . $s => Mdl_invoice_custom::$positions,
            $p . 'payment' . $s => Mdl_payment_custom::$positions,
            $p . 'quote' . $s   => Mdl_quote_custom::$positions,
            $p . 'user' . $s    => Mdl_user_custom::$positions,
        ];

        foreach ($positions as $key => $val) {
            foreach ($val as $key2 => $val2) {
                $val[$key2] = trans($val2);
            }

            $positions[$key] = $val;
        }

        return $positions;
*/
    }

    /**
     * @param $column
     *
     * @return $this
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_by_id()
     */
    public function get_by_id($column)
    {
/*
        $this->where('custom_field_id', $column);

        return $this->get()->row();
*/
    }

    /**
     * Get custom tables list.
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function custom_tables()
     *
     * @return array
     */
    public function getCustomTables(): array
    {
        return [
            'ip_client_custom'  => trans('clients'),
            'ip_invoice_custom' => trans('invoices'),
            'ip_payment_custom' => trans('payments'),
            'ip_quote_custom'   => trans('quotes'),
            'ip_user_custom'    => trans('users'),
        ];
    }

    /**
     * Get custom field types.
     *
     * @return array
     */
    public function getCustomTypes(): array
    {
        return [
            'text'     => trans('text_input'),
            'textarea' => trans('textarea'),
            'checkbox' => trans('checkbox'),
            'date'     => trans('date'),
            'select'   => trans('dropdown'),
        ];
    }

    /**
     * Get nice name for form element.
     *
     * @param string $element
     *
     * @return string
     */
    public function getNicename(string $element): string
    {
        $nicenames = [
            'ip_client_custom'  => 'client',
            'ip_invoice_custom' => 'invoice',
            'ip_payment_custom' => 'payment',
            'ip_quote_custom'   => 'quote',
            'ip_user_custom'    => 'user',
        ];

        return $nicenames[$element] ?? '';
    }

    /**
     * Get custom fields by table name.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTable(string $tableName): \Illuminate\Database\Eloquent\Collection
    {
        return CustomField::query()->where('custom_field_table', $tableName)->get();
    }

    /**
     * Get custom fields by table name ordered by custom_field_order.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTableOrdered(string $tableName): \Illuminate\Database\Eloquent\Collection
    {
        return CustomField::query()
            ->where('custom_field_table', $tableName)
            ->orderBy('custom_field_order')
            ->get();
    }

    /**
     * Check if custom fields exist for a table.
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function existsForTable(string $tableName): bool
    {
        return CustomField::query()->where('custom_field_table', $tableName)->exists();
    }

    /**
     * Check if custom field can be deleted.
     *
     * @param int $id Custom field ID
     *
     * @return bool True if custom field can be deleted
     */
    public function canDelete(int $id): bool
    {
        $blockers = $this->getDeletionBlockers($id);

        return $blockers['custom_values'] === 0;
    }

    /**
     * @param $id
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function used()
     */
    public function used($id = null, $get = true)
    {
/*
        if ( ! $id) {
            return;
        }

        $cf   = $this->get_by_id($id);
        $base = strtr($cf->custom_field_table, ['ip_' => '']) . '_field';

        $this->db->from($cf->custom_field_table)
            ->where($base . 'id', $id)
            ->where($base . 'value IS NOT NULL', null, false)
            ->where($base . 'value <> ""');

        return $get ? $this->db->get()->result() : $this->db;
*/
    }

    /**
     * @param $id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function delete()
     */
    public function delete($id): bool
    {
/*
        if ( ! $this->used($id)) {
            $custom_field = $this->get_by_id($id);
            // Remove MULTIPLE|SINGLE CHOICE values
            if (preg_match('/CHOICE/', $custom_field->custom_field_type)) {
                $this->load->model('custom_values/custom_value');
                $this->mdl_custom_values->delete_all_fid($id);
            }

            // Remove reference in custom table
            $base = strtr($custom_field->custom_field_table, ['ip_' => '']) . '_field';
            $this->db->from($custom_field->custom_field_table)->where($base . 'id', $id)->delete($custom_field->custom_field_table);

            parent::delete($id);

            return true;
        }

        return false;
*/
    }

    /**
     * @param $name
     *
     * @return $this
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function by_table_name()
     */
    public function by_table_name($name)
    {
/*
        $table = array_flip($this->custom_tables()); // get ip_*name*_custom
        $this->by_table($table[$name]);

        return $this;
*/
    }

    /**
     * @param $table
     *
     * @return $this
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function by_table()
     */
    public function by_table($table)
    {
/*
        $this->filter_where('custom_field_table', $table);

        return $this;
*/
    }

    /**
     * @param int    $field_id
     * @param string $custom_field_model
     * @param int    $model_id
     *
     * @return string
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_value_for_field()
     */
    public function get_value_for_field($field_id, $custom_field_model, $object)
    {
/*
        $this->load->model('custom_fields/' . $custom_field_model);

        $cf_table      = str_replace('mdl_', '', $custom_field_model);
        $cf_model_name = str_replace('_custom', '', $cf_table);

        $value = $this->{$custom_field_model}
            ->where($cf_table . '_fieldid', $field_id)
            ->where($cf_model_name . '_id', $object->{$cf_model_name . '_id'})
            ->get()->result();

        $value_key            = $cf_table . '_fieldvalue';
        $value_key_serialized = $cf_table . '_fieldvalue_serialized';

        if ( ! isset($value[0]->{$value_key})) {
            return '';
        }

        return is_array($value[0]->{$value_key}) ? $value[0]->{$value_key_serialized} : $value[0]->{$value_key};
*/
    }

    /**
     * @param string $custom_field_model
     * @param int    $model_id
     *
     * @return array
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_values_for_fields()
     */
    public function get_values_for_fields($custom_field_model, $model_id)
    {
/*
        $this->load->model('custom_fields/' . $custom_field_model);
        $this->load->model('custom_values/custom_value');

        $fields = $this->{$custom_field_model}->by_id($model_id)->get()->result();

        if (empty($fields)) {
            return [];
        }

        $values       = [];
        $custom_field = str_replace('mdl_', '', $custom_field_model);

        foreach ($fields as $field) {
            // Get the custom field value
            $field_id_fieldlabel = $custom_field . '_fieldvalue';

            // Check if exist !(null or '')
            if ( ! $field->{$field_id_fieldlabel}) {
                $values[$field->custom_field_label] = null; // $field->$field_id_fieldlabel
                continue;
            }

            if ($field->custom_field_type == 'MULTIPLE-CHOICE') {
                $custom_values = $this->mdl_custom_values->get_by_ids($field->{$field_id_fieldlabel})->result();

                if ( ! empty($custom_values)) {
                    $key_serialized = $field_id_fieldlabel . '_serialized';

                    $field->{$field_id_fieldlabel} = [];
                    $field->{$key_serialized}      = '';

                    foreach ($custom_values as $custom_value) {
                        //Fix compatibility issue with php 5.6
                        $field->{$field_id_fieldlabel}[] = $custom_value->custom_values_value;

                        // Add as serialized string
                        $field->{$key_serialized} .= $custom_value->custom_values_value;
                        $field->{$key_serialized} .= $custom_value === end($custom_values) ? '' : ', ';
                    }
                }
            } elseif ($field->custom_field_type == 'SINGLE-CHOICE') {
                $custom_value = $this->mdl_custom_values->get_by_id($field->{$field_id_fieldlabel})->result();

                if ( ! empty($custom_value)) {
                    $custom_value                  = $custom_value[0];
                    $field->{$field_id_fieldlabel} = $custom_value->custom_values_value;
                }
            }

            $values[$field->custom_field_label] = $field->{$field_id_fieldlabel};
        }

        return $values;
*/
    }

    /**
     * @param string $table_name
     * @param string $old_column_name
     * @param string $new_column_name
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function rename_column()
     */
    private function rename_column($table_name, $old_column_name, $new_column_name)
    {
/*
        $this->load->dbforge();

        $column = [
            $old_column_name => [
                'name'       => $new_column_name,
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
        ];

        $this->dbforge->modify_column($table_name, $column);
*/
    }

    /**
     * @param string $table_name
     * @param string $column_name
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function add_column()
     */
    private function add_column($table_name, $column_name)
    {
/*
        $this->load->dbforge();

        $column = [
            $column_name => [
                'type'       => 'VARCHAR',
                'constraint' => 256,
            ],
        ];

        $this->dbforge->add_column($table_name, $column);
*/
    }

    /**
     * Get deletion blockers for custom field.
     *
     * @param int $id Custom field ID
     *
     * @return array Array of blocker counts
     */
    public function getDeletionBlockers(int $id): array
    {
        return [
            'custom_values' => \Modules\Core\Models\CustomValue::query()
                ->where('custom_values_field', $id)
                ->count(),
        ];
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return CustomField::class;
    }
}
