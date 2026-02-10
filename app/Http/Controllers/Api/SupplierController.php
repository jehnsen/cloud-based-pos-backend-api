<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Http\Resources\ProductResource;
use App\Models\Supplier;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::where('store_id', Auth::user()->store_id)
            ->withCount('purchaseOrders');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('contact_person', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $suppliers = $query->paginate($perPage);

        return $this->paginatedResponse(
            $suppliers->setCollection(
                $suppliers->getCollection()->map(fn($supplier) => new SupplierResource($supplier))
            ),
            'Suppliers retrieved successfully'
        );
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create([
            'store_id' => Auth::user()->store_id,
            'name' => $request->input('name'),
            'contact_person' => $request->input('contact_person'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'mobile' => $request->input('alternate_phone'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'postal_code' => $request->input('postal_code'),
            'tin' => $request->input('tin'),
            'payment_terms_days' => $request->input('payment_terms_days'),
            'is_active' => $request->input('is_active', true),
            'notes' => $request->input('notes'),
        ]);

        return $this->successResponse(
            new SupplierResource($supplier),
            'Supplier created successfully',
            201
        );
    }

    /**
     * Display the specified supplier.
     */
    public function show(string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->withCount(['purchaseOrders', 'products'])
            ->firstOrFail();

        // Add statistics
        $supplier->total_purchases_amount = $supplier->purchaseOrders()
            ->whereIn('status', ['received', 'partial'])
            ->sum('total_amount');

        $supplier->last_purchase_date = $supplier->purchaseOrders()
            ->orderBy('order_date', 'desc')
            ->value('order_date');

        return $this->successResponse(
            new SupplierResource($supplier),
            'Supplier retrieved successfully'
        );
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $supplier->update($request->only([
            'name',
            'contact_person',
            'email',
            'phone',
            'address',
            'city',
            'province',
            'postal_code',
            'tin',
            'payment_terms_days',
            'is_active',
            'notes',
        ]) + [
            'mobile' => $request->input('alternate_phone'),
        ]);

        return $this->successResponse(
            new SupplierResource($supplier->fresh()),
            'Supplier updated successfully'
        );
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        // Check if supplier has purchase orders
        $poCount = $supplier->purchaseOrders()->count();

        // Soft delete the supplier
        $supplier->delete();

        $message = 'Supplier deleted successfully';
        if ($poCount > 0) {
            $message .= " (had {$poCount} purchase orders)";
        }

        return $this->successResponse(null, $message);
    }

    /**
     * Get products supplied by this supplier.
     */
    public function products(string $uuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $products = $supplier->products()
            ->with(['category', 'unit'])
            ->get();

        return $this->successResponse(
            ProductResource::collection($products),
            'Supplier products retrieved successfully'
        );
    }

    /**
     * Add a product to supplier.
     */
    public function addProduct(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'string'],
            'supplier_price' => ['nullable', 'integer', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'is_preferred' => ['nullable', 'boolean'],
        ]);

        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $product = Product::where('uuid', $request->input('product_id'))
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        // Check if already exists
        if ($supplier->products()->where('product_id', $product->id)->exists()) {
            return $this->errorResponse('Product is already associated with this supplier', null, 409);
        }

        // Attach product to supplier
        $supplier->products()->attach($product->id, [
            'supplier_price' => $request->input('supplier_price'),
            'lead_time_days' => $request->input('lead_time_days'),
            'is_preferred' => $request->boolean('is_preferred', false),
        ]);

        return $this->successResponse(
            null,
            'Product added to supplier successfully'
        );
    }

    /**
     * Remove a product from supplier.
     */
    public function removeProduct(string $uuid, string $productUuid): JsonResponse
    {
        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $product = Product::where('uuid', $productUuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        $supplier->products()->detach($product->id);

        return $this->successResponse(
            null,
            'Product removed from supplier successfully'
        );
    }

    /**
     * Get price history for supplier's products.
     */
    public function priceHistory(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'product_id' => ['nullable', 'string'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
        ]);

        $supplier = Supplier::where('uuid', $uuid)
            ->where('store_id', Auth::user()->store_id)
            ->firstOrFail();

        // Get purchase order items for this supplier
        $query = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            ->join('products as p', 'poi.product_id', '=', 'p.id')
            ->where('po.supplier_id', $supplier->id)
            ->whereIn('po.status', ['submitted', 'partial', 'received'])
            ->select(
                'p.uuid as product_uuid',
                'p.name as product_name',
                'p.sku as product_sku',
                'poi.unit_price',
                'poi.quantity_ordered',
                'po.order_date',
                'po.po_number'
            );

        // Filter by product if specified
        if ($request->filled('product_id')) {
            $product = Product::where('uuid', $request->input('product_id'))
                ->where('store_id', Auth::user()->store_id)
                ->firstOrFail();

            $query->where('poi.product_id', $product->id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('po.order_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('po.order_date', '<=', $request->input('to_date'));
        }

        $priceHistory = $query->orderBy('po.order_date', 'desc')->get();

        // Convert unit_price from centavos to pesos
        $priceHistory = $priceHistory->map(function ($item) {
            $item->unit_price = $item->unit_price / 100;
            return $item;
        });

        return $this->successResponse(
            $priceHistory,
            'Price history retrieved successfully'
        );
    }
}
