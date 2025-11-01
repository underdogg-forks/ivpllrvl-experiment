<?php

namespace Modules\Core\Services;

/**
 * BaseService.
 *
 * Abstract base class for all services providing common functionality
 */
abstract class BaseService
{
    /**
     * Get the model class name that this service manages.
     *
     * @return string|null
     */
    protected function getModelClass(): ?string
    {
        return null;
    }

    /**
     * Create a new model instance.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data)
    {
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            throw new \RuntimeException('Model class not defined in service');
        }

        return $modelClass::create($data);
    }

    /**
     * Update an existing model instance.
     *
     * @param int   $id
     * @param array $data
     *
     * @return mixed
     */
    public function update(int $id, array $data)
    {
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            throw new \RuntimeException('Model class not defined in service');
        }

        $model = $modelClass::findOrFail($id);
        $model->update($data);

        return $model;
    }

    /**
     * Delete a model instance.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            throw new \RuntimeException('Model class not defined in service');
        }

        $model = $modelClass::findOrFail($id);

        return $model->delete();
    }

    /**
     * Find a model by ID.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find(int $id)
    {
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            throw new \RuntimeException('Model class not defined in service');
        }

        return $modelClass::find($id);
    }

    /**
     * Find a model by ID or fail.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findOrFail(int $id)
    {
        $modelClass = $this->getModelClass();
        
        if (!$modelClass) {
            throw new \RuntimeException('Model class not defined in service');
        }

        return $modelClass::findOrFail($id);
    }
}
