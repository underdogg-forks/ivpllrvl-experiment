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
        return \Modules\Core\Models\CustomValue::custom_types();
    }

    /**
     * @param $table
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_by_table()
     */
    public function get_by_table($table)
    {
        return CustomField::query()->where('custom_field_table', $table)->get();
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
        $db_array = $db_array ?: $this->db_array();

        if ($id) {
            $original_record = CustomField::query()->find($id);
        }

        return parent::save($id, $db_array);
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
        $models = [
            'client'  => \Modules\Core\Models\ClientCustom::class,
            'invoice' => \Modules\Core\Models\InvoiceCustom::class,
            'payment' => \Modules\Core\Models\PaymentCustom::class,
            'quote'   => \Modules\Core\Models\QuoteCustom::class,
            'user'    => \Modules\Core\Models\UserCustom::class,
        ];

        $p = $table_name ? 'ip_' : '';
        $s = $table_name ? '_custom' : '';

        $positions = [];
        foreach ($models as $key => $model) {
            $positions[$p . $key . $s] = $model::$positions;
            foreach ($positions[$p . $key . $s] as $k => $v) {
                $positions[$p . $key . $s][$k] = trans($v);
            }
        }

        return $positions;
    }

    /**
     * @param $column
     *
     * @return \Modules\Core\Models\CustomField|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function get_by_id()
     */
    public function get_by_id($column)
    {
        return CustomField::query()->find($column);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Support\Collection|null
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function used()
     */
    public function used($id = null, $get = true)
    {
        if ( ! $id) {
            return;
        }

        $cf   = $this->get_by_id($id);
        $base = str_replace('ip_', '', $cf->custom_field_table) . '_field';

        $query = \Illuminate\Support\Facades\DB::table($cf->custom_field_table)
            ->whereNotNull($base . 'value')
            ->where($base . 'value', '<>', '');

        return $get ? $query->get() : $query;
    }

    /**
     * @param $id
     *
     * @return bool
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/custom_fields/models/Mdl_custom_field.php
     *
     * @legacy-function delete()
     */
    public function delete($id): bool
    {
        if ( ! $this->used($id)->isNotEmpty()) {
            $custom_field = $this->get_by_id($id);

            if (preg_match('/CHOICE/', $custom_field->custom_field_type)) {
                \Modules\Core\Models\CustomValue::query()
                    ->where('custom_values_field', $id)
                    ->delete();
            }

            $base = str_replace('ip_', '', $custom_field->custom_field_table) . '_field';
            \Illuminate\Support\Facades\DB::table($custom_field->custom_field_table)
                ->where($base . 'id', $id)
                ->delete();

            parent::delete($id);

            return true;
        }

        return false;
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
        $table = array_flip($this->getCustomTables());

        return $this->by_table($table[$name]);
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
        return $this->get_by_table($table);
    }

    /**
     * @param int    $field_id
     * @param string $custom_field_model
     * @param object $object
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
        $modelClass    = '\\Modules\\Core\\Models\\' . str_replace('mdl_', '', $custom_field_model);
        $cf_table      = str_replace('mdl_', '', $custom_field_model);
        $cf_model_name = str_replace('_custom', '', $cf_table);

        $value = $modelClass::query()
            ->where($cf_table . '_fieldid', $field_id)
            ->where($cf_model_name . '_id', $object->{$cf_model_name . '_id'})
            ->get();

        $value_key            = $cf_table . '_fieldvalue';
        $value_key_serialized = $cf_table . '_fieldvalue_serialized';

        if (empty($value->first()->{$value_key})) {
            return '';
        }

        return is_array($value->first()->{$value_key})
            ? $value->first()->{$value_key_serialized}
            : $value->first()->{$value_key};
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
        $modelClass = '\\Modules\\Core\\Models\\' . str_replace('mdl_', '', $custom_field_model);
        $fields     = $modelClass::query()->where($modelClass::$primaryKey, $model_id)->get();

        if ($fields->isEmpty()) {
            return [];
        }

        $values              = [];
        $custom_field_prefix = str_replace('mdl_', '', $custom_field_model);

        foreach ($fields as $field) {
            $field_id_fieldlabel = $custom_field_prefix . '_fieldvalue';

            if ( ! $field->{$field_id_fieldlabel}) {
                $values[$field->custom_field_label] = null;
                continue;
            }

            if ($field->custom_field_type == 'MULTIPLE-CHOICE') {
                $custom_values = \Modules\Core\Models\CustomValue::query()
                    ->whereIn('custom_values_id', $field->{$field_id_fieldlabel})
                    ->get();

                $field->{$field_id_fieldlabel}                 = [];
                $field->{$field_id_fieldlabel . '_serialized'} = '';

                foreach ($custom_values as $custom_value) {
                    $field->{$field_id_fieldlabel}[] = $custom_value->custom_values_value;
                    $field->{$field_id_fieldlabel . '_serialized'} .= $custom_value->custom_values_value;
                    $field->{$field_id_fieldlabel . '_serialized'} .= $custom_value === $custom_values->last() ? '' : ', ';
                }
            } elseif ($field->custom_field_type == 'SINGLE-CHOICE') {
                $custom_value = \Modules\Core\Models\CustomValue::query()
                    ->find($field->{$field_id_fieldlabel});
                if ($custom_value) {
                    $field->{$field_id_fieldlabel} = $custom_value->custom_values_value;
                }
            }

            $values[$field->custom_field_label] = $field->{$field_id_fieldlabel};
        }

        return $values;
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
        $schema = \Illuminate\Support\Facades\Schema::connection();
        $schema->table($table_name, function ($table) use ($old_column_name, $new_column_name) {
            $table->renameColumn($old_column_name, $new_column_name);
        });
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
        $schema = \Illuminate\Support\Facades\Schema::connection();
        $schema->table($table_name, function ($table) use ($column_name) {
            $table->string($column_name, 256)->nullable();
        });
    }
}
