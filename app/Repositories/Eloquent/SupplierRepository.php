<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use App\Models\Product;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    protected function model(): string
    {
        return Supplier::class;
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where(function ($q) use ($query) {
                $q->where('company_name', 'LIKE', "%{$query}%")
                    ->orWhere('contact_person', 'LIKE', "%{$query}%")
                    ->orWhere('contact_person_phone', 'LIKE', "%{$query}%")
                    ->orWhere('code', 'LIKE', "%{$query}%");
            })
            ->orderBy('company_name')
            ->paginate($perPage);
    }

    public function getWithPurchaseOrderCount(): Collection
    {
        return $this->newQuery()
            ->withCount('purchaseOrders')
            ->orderBy('company_name')
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->newQuery()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();
    }

    public function findByCode(string $code): ?Supplier
    {
        return $this->newQuery()
            ->where('code', $code)
            ->first();
    }

    public function getSupplierProducts(string $supplierUuid): Collection
    {
        $supplier = $this->findByUuidOrFail($supplierUuid);

        return $supplier->products()
            ->with(['category', 'unit'])
            ->get();
    }

    public function linkProduct(string $supplierUuid, string $productUuid, array $data): bool
    {
        $supplier = $this->findByUuidOrFail($supplierUuid);
        $product = Product::where('uuid', $productUuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $supplier->products()->attach($product->id, $data);

        return true;
    }

    public function unlinkProduct(string $supplierUuid, string $productUuid): bool
    {
        $supplier = $this->findByUuidOrFail($supplierUuid);
        $product = Product::where('uuid', $productUuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $supplier->products()->detach($product->id);

        return true;
    }

    public function getPriceHistory(string $supplierUuid, string $productUuid, int $months = 12): Collection
    {
        $supplier = $this->findByUuidOrFail($supplierUuid);
        $product = Product::where('uuid', $productUuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $cutoffDate = Carbon::now()->subMonths($months);

        return DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            ->where('po.supplier_id', $supplier->id)
            ->where('poi.product_id', $product->id)
            ->where('po.status', '!=', 'cancelled')
            ->where('po.order_date', '>=', $cutoffDate)
            ->select(
                'po.po_number',
                'po.order_date',
                'poi.unit_price',
                'poi.quantity_ordered',
                'poi.quantity_received',
                'po.status'
            )
            ->orderBy('po.order_date', 'desc')
            ->get();
    }
}
