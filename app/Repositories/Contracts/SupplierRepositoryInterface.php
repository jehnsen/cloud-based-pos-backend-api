<?php

namespace App\Repositories\Contracts;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Search suppliers by name, contact, phone
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get with purchase order count
     */
    public function getWithPurchaseOrderCount(): Collection;

    /**
     * Get active suppliers
     */
    public function getActive(): Collection;

    /**
     * Find by supplier code
     */
    public function findByCode(string $code): ?Supplier;

    /**
     * Get supplier's linked products
     */
    public function getSupplierProducts(string $supplierUuid): Collection;

    /**
     * Link product to supplier
     */
    public function linkProduct(string $supplierUuid, string $productUuid, array $data): bool;

    /**
     * Unlink product from supplier
     */
    public function unlinkProduct(string $supplierUuid, string $productUuid): bool;

    /**
     * Get price history for a product from this supplier
     */
    public function getPriceHistory(string $supplierUuid, string $productUuid, int $months = 12): Collection;
}
