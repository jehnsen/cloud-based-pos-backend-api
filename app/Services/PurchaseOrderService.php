<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\ActivityLog;
use App\Events\PurchaseOrderReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseOrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new purchase order.
     *
     * @param array $data
     * @return PurchaseOrder
     * @throws \Exception
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $store = $user->store;

            // Get supplier
            $supplier = Supplier::where('uuid', $data['supplier_id'])
                ->where('store_id', $store->id)
                ->firstOrFail();

            // Generate PO number
            $poNumber = $this->generatePoNumber($store);

            // Load and validate products
            $products = [];
            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::where('uuid', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $lineTotal;

                $products[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $lineTotal,
                ];
            }

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'store_id' => $store->id,
                'supplier_id' => $supplier->id,
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'po_number' => $poNumber,
                'status' => 'draft',
                'order_date' => Carbon::now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'total_amount' => $totalAmount / 100, // Accessor will convert to centavos
                'notes' => $data['notes'] ?? null,
            ]);

            // Create purchase order items
            foreach ($products as $productData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $productData['product']->id,
                    'product_name' => $productData['product']->name,
                    'product_sku' => $productData['product']->sku,
                    'quantity_ordered' => $productData['quantity'],
                    'quantity_received' => 0,
                    'unit_price' => $productData['unit_cost'] / 100, // Accessor will convert to centavos
                    'line_total' => $productData['line_total'] / 100, // Accessor will convert to centavos
                ]);
            }

            // Log activity
            ActivityLog::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
                'action' => 'purchase_order_created',
                'description' => "Purchase order {$poNumber} created for supplier {$supplier->name}",
                'model_type' => PurchaseOrder::class,
                'model_id' => $purchaseOrder->id,
            ]);

            // Load relationships and return
            return $purchaseOrder->load(['supplier', 'purchaseOrderItems.product.unit', 'user']);
        });
    }

    /**
     * Update an existing purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param array $data
     * @return PurchaseOrder
     * @throws \Exception
     */
    public function updatePurchaseOrder(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        // Validate status is draft
        if ($purchaseOrder->status !== 'draft') {
            throw new \Exception("Only draft purchase orders can be updated. Current status: {$purchaseOrder->status}");
        }

        return DB::transaction(function () use ($purchaseOrder, $data) {
            $user = Auth::user();
            $store = $user->store;

            // Update supplier if provided
            if (isset($data['supplier_id'])) {
                $supplier = Supplier::where('uuid', $data['supplier_id'])
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                $purchaseOrder->supplier_id = $supplier->id;
            }

            // Update header fields
            if (isset($data['expected_delivery_date'])) {
                $purchaseOrder->expected_delivery_date = $data['expected_delivery_date'];
            }

            if (isset($data['notes'])) {
                $purchaseOrder->notes = $data['notes'];
            }

            // Update items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $purchaseOrder->purchaseOrderItems()->delete();

                // Create new items and calculate total
                $totalAmount = 0;

                foreach ($data['items'] as $item) {
                    $product = Product::where('uuid', $item['product_id'])
                        ->where('store_id', $store->id)
                        ->firstOrFail();

                    $lineTotal = $item['quantity'] * $item['unit_cost'];
                    $totalAmount += $lineTotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity_ordered' => $item['quantity'],
                        'quantity_received' => 0,
                        'unit_price' => $item['unit_cost'] / 100, // Accessor will convert to centavos
                        'line_total' => $lineTotal / 100, // Accessor will convert to centavos
                    ]);
                }

                $purchaseOrder->total_amount = $totalAmount / 100; // Accessor will convert to centavos
            }

            $purchaseOrder->save();

            // Log activity
            ActivityLog::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
                'action' => 'purchase_order_updated',
                'description' => "Purchase order {$purchaseOrder->po_number} updated",
                'model_type' => PurchaseOrder::class,
                'model_id' => $purchaseOrder->id,
            ]);

            // Load relationships and return
            return $purchaseOrder->fresh(['supplier', 'purchaseOrderItems.product.unit', 'user']);
        });
    }

    /**
     * Submit a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrder
     * @throws \Exception
     */
    public function submitPurchaseOrder(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'draft') {
            throw new \Exception("Only draft purchase orders can be submitted. Current status: {$purchaseOrder->status}");
        }

        $purchaseOrder->update([
            'status' => 'submitted',
        ]);

        // Log activity
        ActivityLog::create([
            'store_id' => $purchaseOrder->store_id,
            'user_id' => Auth::id(),
            'action' => 'purchase_order_submitted',
            'description' => "Purchase order {$purchaseOrder->po_number} submitted to supplier",
            'model_type' => PurchaseOrder::class,
            'model_id' => $purchaseOrder->id,
        ]);

        return $purchaseOrder->fresh(['supplier', 'purchaseOrderItems.product.unit', 'user']);
    }

    /**
     * Receive items from a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param array $items
     * @param string|null $notes
     * @return PurchaseOrder
     * @throws \Exception
     */
    public function receivePurchaseOrder(PurchaseOrder $purchaseOrder, array $items, ?string $notes = null): PurchaseOrder
    {
        // Validate PO status
        if (!in_array($purchaseOrder->status, ['submitted', 'partial'])) {
            throw new \Exception("Purchase order must be submitted or partially received. Current status: {$purchaseOrder->status}");
        }

        return DB::transaction(function () use ($purchaseOrder, $items, $notes) {
            $user = Auth::user();

            foreach ($items as $itemData) {
                // Find purchase order item
                $poItem = PurchaseOrderItem::findOrFail($itemData['purchase_order_item_id']);

                // Validate item belongs to this PO
                if ($poItem->purchase_order_id !== $purchaseOrder->id) {
                    throw new \Exception("Item does not belong to this purchase order.");
                }

                // Calculate remaining quantity
                $remainingQuantity = $poItem->quantity_ordered - $poItem->quantity_received;
                $quantityToReceive = $itemData['quantity_received'];

                // Validate quantity
                if ($quantityToReceive > $remainingQuantity) {
                    throw new \Exception("Quantity to receive ({$quantityToReceive}) exceeds remaining quantity ({$remainingQuantity}).");
                }

                // Update quantity received
                $poItem->quantity_received += $quantityToReceive;
                $poItem->save();

                // Add to inventory
                $product = $poItem->product;
                if ($product && $product->track_inventory) {
                    $this->inventoryService->adjustStock(
                        $product,
                        'add',
                        $quantityToReceive,
                        'Purchase Order Receipt',
                        "PO: {$purchaseOrder->po_number} - Received {$quantityToReceive} units",
                        $user->branch
                    );

                    // Optionally update product cost price with latest supplier price
                    // Only update if the new cost is different
                    $newCost = $poItem->unit_price * 100; // Convert to centavos
                    if ($product->cost_price !== $newCost) {
                        $product->update(['cost_price' => $newCost]);
                    }
                }
            }

            // Determine new PO status
            $allItems = $purchaseOrder->purchaseOrderItems;
            $allFullyReceived = $allItems->every(function ($item) {
                return $item->quantity_received >= $item->quantity_ordered;
            });

            $anyPartiallyReceived = $allItems->contains(function ($item) {
                return $item->quantity_received > 0 && $item->quantity_received < $item->quantity_ordered;
            });

            if ($allFullyReceived) {
                $purchaseOrder->status = 'received';
                $purchaseOrder->received_date = Carbon::now();
            } elseif ($anyPartiallyReceived || $allItems->contains(fn($item) => $item->quantity_received > 0)) {
                $purchaseOrder->status = 'partial';
            }

            $purchaseOrder->save();

            // Log activity
            ActivityLog::create([
                'store_id' => $purchaseOrder->store_id,
                'user_id' => $user->id,
                'action' => 'purchase_order_received',
                'description' => "Items received for PO {$purchaseOrder->po_number}. Status: {$purchaseOrder->status}. {$notes}",
                'model_type' => PurchaseOrder::class,
                'model_id' => $purchaseOrder->id,
            ]);

            // Dispatch event
            event(new PurchaseOrderReceived($purchaseOrder));

            return $purchaseOrder->fresh(['supplier', 'purchaseOrderItems.product.unit', 'user']);
        });
    }

    /**
     * Cancel a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param string $reason
     * @return PurchaseOrder
     * @throws \Exception
     */
    public function cancelPurchaseOrder(PurchaseOrder $purchaseOrder, string $reason): PurchaseOrder
    {
        // Validate status
        if (!in_array($purchaseOrder->status, ['draft', 'submitted'])) {
            throw new \Exception("Only draft or submitted purchase orders can be cancelled. Current status: {$purchaseOrder->status}");
        }

        $purchaseOrder->update([
            'status' => 'cancelled',
        ]);

        // Log activity
        ActivityLog::create([
            'store_id' => $purchaseOrder->store_id,
            'user_id' => Auth::id(),
            'action' => 'purchase_order_cancelled',
            'description' => "Purchase order {$purchaseOrder->po_number} cancelled. Reason: {$reason}",
            'model_type' => PurchaseOrder::class,
            'model_id' => $purchaseOrder->id,
        ]);

        return $purchaseOrder->fresh(['supplier', 'purchaseOrderItems.product.unit', 'user']);
    }

    /**
     * Generate a unique PO number.
     *
     * @param Store $store
     * @return string
     */
    public function generatePoNumber(Store $store): string
    {
        return DB::transaction(function () use ($store) {
            $year = Carbon::now()->format('Y');
            $prefix = "PO-{$year}-";

            // Get the latest PO number for this year with lock
            $latestPo = PurchaseOrder::where('store_id', $store->id)
                ->where('po_number', 'LIKE', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('po_number', 'desc')
                ->first();

            if ($latestPo) {
                // Extract the sequence number and increment
                $lastNumber = (int) substr($latestPo->po_number, strlen($prefix));
                $nextNumber = $lastNumber + 1;
            } else {
                // First PO of the year
                $nextNumber = 1;
            }

            // Format with 6 digits
            return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }
}
