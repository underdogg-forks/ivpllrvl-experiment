<?php

namespace Modules\Products\Services;

use App\Services\BaseService;
use Modules\Products\Models\Family;

/**
 * FamilyService.
 *
 * Service class for managing product family business logic
 */
class FamilyService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Family::class;
    }

    /**
     * Get all families ordered and paginated.
     *
     * @param int $perPage
     * @param int $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15, int $page = 0): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Family::ordered()->paginate($perPage, ['*'], 'page', $page);
    }
}
