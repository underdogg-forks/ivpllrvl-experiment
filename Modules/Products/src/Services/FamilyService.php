<?php

namespace Modules\Products\Services;

use Modules\Core\Services\BaseService;
use Modules\Products\Models\Family;
use Modules\Products\Models\Product;

/**
 * FamilyService.
 *
 * Service class for managing product family business logic
 */
class FamilyService extends BaseService
{
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

    /**
     * Check if a family can be deleted.
     *
     * A family cannot be deleted if it has products.
     *
     * @param int $familyId
     *
     * @return bool
     */
    public function canDelete(int $familyId): bool
    {
        return ! Product::query()->where('family_id', $familyId)->exists();
    }

    /**
     * Get deletion blocker details for a family.
     *
     * @param int $familyId
     *
     * @return array
     */
    public function getDeletionBlockers(int $familyId): array
    {
        return [
            'products' => Product::query()->where('family_id', $familyId)->count(),
        ];
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return Family::class;
    }
}
