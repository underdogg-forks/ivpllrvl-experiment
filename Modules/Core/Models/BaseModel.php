<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Base Model for the application
 * Replaces CodeIgniter's MY_Model with Eloquent Model
 */
abstract class BaseModel extends EloquentModel
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'default';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * Get records with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15)
    {
        return static::query()->paginate($perPage);
    }

    /**
     * Get all records
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return static::all();
    }

    /**
     * Get a record by ID
     *
     * @param mixed $id
     * @return static|null
     */
    public function getById($id)
    {
        return static::find($id);
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return static
     */
    public function createRecord(array $data)
    {
        return static::create($data);
    }

    /**
     * Update a record
     *
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function updateRecord($id, array $data)
    {
        $record = static::find($id);
        if ($record) {
            return $record->update($data);
        }
        return false;
    }

    /**
     * Delete a record
     *
     * @param mixed $id
     * @return bool|null
     */
    public function deleteRecord($id)
    {
        $record = static::find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    /**
     * Check if a record exists
     *
     * @param mixed $id
     * @return bool
     */
    public function exists($id)
    {
        return static::where($this->getKeyName(), $id)->exists();
    }
}
