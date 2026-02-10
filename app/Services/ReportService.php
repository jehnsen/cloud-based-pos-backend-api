<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockAdjustment;
use App\Models\Store;
use App\Models\SupplierProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    protected CreditService $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Get daily sales report for specific date.
     *
     * @param Store $store
     * @param Carbon $date
     * @return array
     */
    public function dailySales(Store $store, Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get hourly sales breakdown
        $hourlySales = Sale::where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startOfDay, $endOfDay])
            ->select(
                DB::raw('HOUR(sale_date) as hour'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($row) {
                return [
                    'hour' => $row->hour,
                    'transaction_count' => $row->transaction_count,
                    'total_sales' => $row->total_sales / 100,
                ];
            });

        // Payment methods breakdown
        $paymentMethods = SalePayment::whereHas('sale', function ($query) use ($store, $startOfDay, $endOfDay) {
            $query->where('store_id', $store->id)
                ->where('status', 'completed')
                ->whereBetween('sale_date', [$startOfDay, $endOfDay]);
        })
            ->select(
                'method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('method')
            ->get()
            ->map(function ($row) {
                return [
                    'method' => $row->method,
                    'count' => $row->count,
                    'total' => $row->total / 100,
                ];
            });

        // Top products sold today
        $topProducts = SaleItem::whereHas('sale', function ($query) use ($store, $startOfDay, $endOfDay) {
            $query->where('store_id', $store->id)
                ->where('status', 'completed')
                ->whereBetween('sale_date', [$startOfDay, $endOfDay]);
        })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_revenue')
            )
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'product_id' => $row->product_id,
                    'product_name' => $row->product->name ?? 'N/A',
                    'product_sku' => $row->product->sku ?? 'N/A',
                    'quantity_sold' => $row->total_quantity,
                    'revenue' => $row->total_revenue / 100,
                ];
            });

        // Overall summary
        $summary = Sale::where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startOfDay, $endOfDay])
            ->select(
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(discount_amount) as total_discounts'),
                DB::raw('AVG(total_amount) as average_transaction')
            )
            ->first();

        return [
            'date' => $date->toDateString(),
            'summary' => [
                'transaction_count' => $summary->transaction_count ?? 0,
                'total_sales' => ($summary->total_sales ?? 0) / 100,
                'total_discounts' => ($summary->total_discounts ?? 0) / 100,
                'average_transaction' => ($summary->average_transaction ?? 0) / 100,
            ],
            'hourly_breakdown' => $hourlySales->toArray(),
            'payment_methods' => $paymentMethods->toArray(),
            'top_products' => $topProducts->toArray(),
        ];
    }

    /**
     * Get sales summary for date range with grouping.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @param string $groupBy 'day', 'week', or 'month'
     * @return array
     */
    public function salesSummary(Store $store, Carbon $from, Carbon $to, string $groupBy = 'day'): array
    {
        // Determine date format for grouping
        $dateFormat = match ($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        // Get grouped sales data
        $salesData = Sale::where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->select(
                DB::raw("DATE_FORMAT(sale_date, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(discount_amount) as total_discounts'),
                DB::raw('AVG(total_amount) as average_transaction')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                return [
                    'period' => $row->period,
                    'transaction_count' => $row->transaction_count,
                    'total_sales' => $row->total_sales / 100,
                    'total_discounts' => $row->total_discounts / 100,
                    'average_transaction' => $row->average_transaction / 100,
                ];
            });

        // Calculate overall summary
        $overallSummary = Sale::where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(discount_amount) as total_discounts'),
                DB::raw('AVG(total_amount) as average_transaction')
            )
            ->first();

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
                'group_by' => $groupBy,
            ],
            'summary' => [
                'total_transactions' => $overallSummary->total_transactions ?? 0,
                'total_sales' => ($overallSummary->total_sales ?? 0) / 100,
                'total_discounts' => ($overallSummary->total_discounts ?? 0) / 100,
                'average_transaction' => ($overallSummary->average_transaction ?? 0) / 100,
            ],
            'data' => $salesData->toArray(),
        ];
    }

    /**
     * Get sales breakdown by category.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @return array
     */
    public function salesByCategory(Store $store, Carbon $from, Carbon $to): array
    {
        $categorySales = SaleItem::whereHas('sale', function ($query) use ($store, $from, $to) {
            $query->where('store_id', $store->id)
                ->where('status', 'completed')
                ->whereBetween('sale_date', [$from, $to]);
        })
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_sales'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as transaction_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales')
            ->get();

        // Calculate total for percentages
        $grandTotal = $categorySales->sum(function ($row) {
            return $row->total_sales;
        });

        $data = $categorySales->map(function ($row) use ($grandTotal) {
            $totalSalesPesos = $row->total_sales / 100;
            return [
                'category_id' => $row->category_id,
                'category_name' => $row->category_name,
                'total_quantity' => $row->total_quantity,
                'total_sales' => $totalSalesPesos,
                'transaction_count' => $row->transaction_count,
                'percentage' => $grandTotal > 0 ? round(($row->total_sales / $grandTotal) * 100, 2) : 0,
            ];
        });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'total_sales' => $grandTotal / 100,
            'data' => $data->toArray(),
        ];
    }

    /**
     * Get sales analysis by customer.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @param int $limit
     * @return array
     */
    public function salesByCustomer(Store $store, Carbon $from, Carbon $to, int $limit = 50): array
    {
        $customerSales = Sale::where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereNotNull('customer_id')
            ->whereBetween('sale_date', [$from, $to])
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_purchases'),
                DB::raw('AVG(total_amount) as average_order_value'),
                DB::raw('MAX(sale_date) as last_purchase_date')
            )
            ->with('customer:id,uuid,code,name,email,phone')
            ->groupBy('customer_id')
            ->orderByDesc('total_purchases')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'customer' => $row->customer ? [
                        'uuid' => $row->customer->uuid,
                        'code' => $row->customer->code,
                        'name' => $row->customer->name,
                        'email' => $row->customer->email,
                        'phone' => $row->customer->phone,
                    ] : null,
                    'transaction_count' => $row->transaction_count,
                    'total_purchases' => $row->total_purchases / 100,
                    'average_order_value' => $row->average_order_value / 100,
                    'last_purchase_date' => $row->last_purchase_date,
                ];
            });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'data' => $customerSales->toArray(),
        ];
    }

    /**
     * Get sales breakdown by payment method.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @return array
     */
    public function salesByPaymentMethod(Store $store, Carbon $from, Carbon $to): array
    {
        $paymentMethodSales = SalePayment::whereHas('sale', function ($query) use ($store, $from, $to) {
            $query->where('store_id', $store->id)
                ->where('status', 'completed')
                ->whereBetween('sale_date', [$from, $to]);
        })
            ->select(
                'method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('method')
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = $paymentMethodSales->sum('total_amount');

        $data = $paymentMethodSales->map(function ($row) use ($grandTotal) {
            return [
                'method' => $row->method,
                'transaction_count' => $row->transaction_count,
                'total_amount' => $row->total_amount / 100,
                'percentage' => $grandTotal > 0 ? round(($row->total_amount / $grandTotal) * 100, 2) : 0,
            ];
        });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'total_amount' => $grandTotal / 100,
            'data' => $data->toArray(),
        ];
    }

    /**
     * Get sales performance by cashier.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @return array
     */
    public function salesByCashier(Store $store, Carbon $from, Carbon $to): array
    {
        $cashierSales = Sale::where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->select(
                'user_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as average_transaction')
            )
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderByDesc('total_sales')
            ->get()
            ->map(function ($row) {
                return [
                    'cashier' => $row->user ? [
                        'id' => $row->user->id,
                        'name' => $row->user->name,
                        'email' => $row->user->email,
                    ] : null,
                    'transaction_count' => $row->transaction_count,
                    'total_sales' => $row->total_sales / 100,
                    'average_transaction' => $row->average_transaction / 100,
                ];
            });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'data' => $cashierSales->toArray(),
        ];
    }

    /**
     * Get inventory valuation report.
     *
     * @param Store $store
     * @return array
     */
    public function inventoryValuation(Store $store): array
    {
        $categoryValuation = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('COUNT(products.id) as product_count'),
                DB::raw('SUM(products.current_stock) as total_units'),
                DB::raw('SUM(products.current_stock * products.cost_price) as total_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->get()
            ->map(function ($row) {
                return [
                    'category_id' => $row->category_id,
                    'category_name' => $row->category_name,
                    'product_count' => $row->product_count,
                    'total_units' => $row->total_units,
                    'total_value' => $row->total_value / 100,
                ];
            });

        $totalValue = $categoryValuation->sum('total_value');
        $totalUnits = $categoryValuation->sum('total_units');
        $totalProducts = $categoryValuation->sum('product_count');

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'total_units' => $totalUnits,
                'total_value' => $totalValue,
            ],
            'data' => $categoryValuation->toArray(),
        ];
    }

    /**
     * Get stock movement history.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @param int|null $productId
     * @return array
     */
    public function stockMovement(Store $store, Carbon $from, Carbon $to, ?int $productId = null): array
    {
        $query = StockAdjustment::where('store_id', $store->id)
            ->whereBetween('created_at', [$from, $to])
            ->with(['product:id,name,sku', 'user:id,name', 'branch:id,name']);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($adjustment) {
                return [
                    'id' => $adjustment->id,
                    'uuid' => $adjustment->uuid,
                    'date' => $adjustment->created_at->format('Y-m-d H:i:s'),
                    'product' => $adjustment->product ? [
                        'id' => $adjustment->product->id,
                        'name' => $adjustment->product->name,
                        'sku' => $adjustment->product->sku,
                    ] : null,
                    'branch' => $adjustment->branch ? [
                        'id' => $adjustment->branch->id,
                        'name' => $adjustment->branch->name,
                    ] : null,
                    'type' => $adjustment->type,
                    'quantity_before' => $adjustment->quantity_before,
                    'quantity_change' => $adjustment->quantity_change,
                    'quantity_after' => $adjustment->quantity_after,
                    'reason' => $adjustment->reason,
                    'notes' => $adjustment->notes,
                    'user' => $adjustment->user ? [
                        'id' => $adjustment->user->id,
                        'name' => $adjustment->user->name,
                    ] : null,
                ];
            });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'product_id' => $productId,
            'data' => $movements->toArray(),
        ];
    }

    /**
     * Get low stock alert report.
     *
     * @param Store $store
     * @return array
     */
    public function lowStockReport(Store $store): array
    {
        $lowStockProducts = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->whereColumn('current_stock', '<=', 'reorder_point')
            ->with(['category:id,name', 'unit:id,name,abbreviation'])
            ->orderByRaw('(current_stock / NULLIF(reorder_point, 0)) ASC')
            ->get()
            ->map(function ($product) {
                // Estimate days until stockout based on recent sales
                $avgDailySales = SaleItem::where('product_id', $product->id)
                    ->whereHas('sale', function ($query) {
                        $query->where('status', 'completed')
                            ->where('sale_date', '>=', now()->subDays(30));
                    })
                    ->avg('quantity') ?? 0;

                $daysUntilStockout = $avgDailySales > 0
                    ? round($product->current_stock / $avgDailySales, 1)
                    : null;

                return [
                    'uuid' => $product->uuid,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'current_stock' => $product->current_stock,
                    'reorder_point' => $product->reorder_point,
                    'minimum_order_qty' => $product->minimum_order_qty,
                    'unit' => $product->unit?->abbreviation,
                    'stock_percentage' => $product->reorder_point > 0
                        ? round(($product->current_stock / $product->reorder_point) * 100, 2)
                        : 0,
                    'estimated_days_until_stockout' => $daysUntilStockout,
                    'urgency' => $product->current_stock <= 0 ? 'critical'
                        : ($product->current_stock <= ($product->reorder_point * 0.5) ? 'high' : 'medium'),
                ];
            });

        return [
            'count' => $lowStockProducts->count(),
            'data' => $lowStockProducts->toArray(),
        ];
    }

    /**
     * Get dead stock report (no sales in X days).
     *
     * @param Store $store
     * @param int $days
     * @return array
     */
    public function deadStockReport(Store $store, int $days = 90): array
    {
        $cutoffDate = now()->subDays($days);

        // Get all products with stock
        $productsWithStock = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->get();

        $deadStockProducts = $productsWithStock->filter(function ($product) use ($cutoffDate) {
            // Check if product has any sales since cutoff date
            $hasSales = SaleItem::where('product_id', $product->id)
                ->whereHas('sale', function ($query) use ($cutoffDate) {
                    $query->where('status', 'completed')
                        ->where('sale_date', '>=', $cutoffDate);
                })
                ->exists();

            return !$hasSales;
        })
            ->map(function ($product) use ($cutoffDate) {
                // Get last sale date
                $lastSale = SaleItem::where('product_id', $product->id)
                    ->whereHas('sale', function ($query) {
                        $query->where('status', 'completed');
                    })
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->orderBy('sales.sale_date', 'desc')
                    ->first();

                $daysSinceLastSale = $lastSale
                    ? now()->diffInDays($lastSale->sale_date)
                    : null;

                $stockValue = $product->current_stock * ($product->getRawOriginal('cost_price') / 100);

                return [
                    'uuid' => $product->uuid,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'current_stock' => $product->current_stock,
                    'cost_price' => $product->cost_price,
                    'stock_value' => $stockValue,
                    'days_since_last_sale' => $daysSinceLastSale,
                    'last_sale_date' => $lastSale?->sale_date,
                ];
            })
            ->sortByDesc('stock_value')
            ->values();

        return [
            'period_days' => $days,
            'count' => $deadStockProducts->count(),
            'total_stock_value' => $deadStockProducts->sum('stock_value'),
            'data' => $deadStockProducts->toArray(),
        ];
    }

    /**
     * Get product profitability analysis.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @param int $limit
     * @return array
     */
    public function productProfitability(Store $store, Carbon $from, Carbon $to, int $limit = 50): array
    {
        $profitability = SaleItem::whereHas('sale', function ($query) use ($store, $from, $to) {
            $query->where('store_id', $store->id)
                ->where('status', 'completed')
                ->whereBetween('sale_date', [$from, $to]);
        })
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.uuid as product_uuid',
                'products.name as product_name',
                'products.sku as product_sku',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost')
            )
            ->groupBy('products.id', 'products.uuid', 'products.name', 'products.sku')
            ->get()
            ->map(function ($row) {
                $revenue = $row->total_revenue / 100;
                $cost = $row->total_cost / 100;
                $profit = $revenue - $cost;
                $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

                return [
                    'product_uuid' => $row->product_uuid,
                    'product_sku' => $row->product_sku,
                    'product_name' => $row->product_name,
                    'quantity_sold' => $row->total_quantity,
                    'total_revenue' => $revenue,
                    'total_cost' => $cost,
                    'gross_profit' => $profit,
                    'margin_percentage' => $margin,
                ];
            })
            ->sortByDesc('gross_profit')
            ->take($limit)
            ->values();

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'summary' => [
                'total_revenue' => $profitability->sum('total_revenue'),
                'total_cost' => $profitability->sum('total_cost'),
                'total_profit' => $profitability->sum('gross_profit'),
            ],
            'data' => $profitability->toArray(),
        ];
    }

    /**
     * Get credit aging report.
     *
     * @param Store $store
     * @return array
     */
    public function creditAgingReport(Store $store): array
    {
        return $this->creditService->getAgingReport($store);
    }

    /**
     * Get collection report (credit payments received).
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @return array
     */
    public function collectionReport(Store $store, Carbon $from, Carbon $to): array
    {
        $collections = CreditTransaction::where('store_id', $store->id)
            ->where('type', 'payment')
            ->whereBetween('transaction_date', [$from, $to])
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                'payment_method',
                DB::raw('COUNT(*) as payment_count'),
                DB::raw('SUM(ABS(amount)) as total_collected')
            )
            ->groupBy('date', 'payment_method')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'payment_method' => $row->payment_method,
                    'payment_count' => $row->payment_count,
                    'total_collected' => $row->total_collected / 100,
                ];
            });

        // Summary by payment method
        $methodSummary = CreditTransaction::where('store_id', $store->id)
            ->where('type', 'payment')
            ->whereBetween('transaction_date', [$from, $to])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as payment_count'),
                DB::raw('SUM(ABS(amount)) as total_collected')
            )
            ->groupBy('payment_method')
            ->get()
            ->map(function ($row) {
                return [
                    'payment_method' => $row->payment_method,
                    'payment_count' => $row->payment_count,
                    'total_collected' => $row->total_collected / 100,
                ];
            });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'summary' => [
                'total_collected' => $methodSummary->sum('total_collected'),
                'total_payments' => $methodSummary->sum('payment_count'),
            ],
            'by_method' => $methodSummary->toArray(),
            'daily_collections' => $collections->toArray(),
        ];
    }

    /**
     * Get purchases by supplier report.
     *
     * @param Store $store
     * @param Carbon $from
     * @param Carbon $to
     * @return array
     */
    public function purchasesBySupplier(Store $store, Carbon $from, Carbon $to): array
    {
        $supplierPurchases = PurchaseOrder::where('store_id', $store->id)
            ->where('status', 'received')
            ->whereBetween('received_date', [$from, $to])
            ->select(
                'supplier_id',
                DB::raw('COUNT(*) as po_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->with('supplier:id,code,name,email,phone')
            ->groupBy('supplier_id')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($row) {
                return [
                    'supplier' => $row->supplier ? [
                        'code' => $row->supplier->code,
                        'name' => $row->supplier->name,
                        'email' => $row->supplier->email,
                        'phone' => $row->supplier->phone,
                    ] : null,
                    'po_count' => $row->po_count,
                    'total_amount' => $row->total_amount / 100,
                ];
            });

        return [
            'period' => [
                'start_date' => $from->toDateString(),
                'end_date' => $to->toDateString(),
            ],
            'summary' => [
                'total_amount' => $supplierPurchases->sum('total_amount'),
                'total_pos' => $supplierPurchases->sum('po_count'),
            ],
            'data' => $supplierPurchases->toArray(),
        ];
    }

    /**
     * Get price comparison report across suppliers.
     *
     * @param Store $store
     * @param int|null $productId
     * @return array
     */
    public function priceComparisonReport(Store $store, ?int $productId = null): array
    {
        $query = SupplierProduct::whereHas('product', function ($q) use ($store) {
            $q->where('store_id', $store->id);
        })
            ->with([
                'product:id,uuid,name,sku,cost_price',
                'supplier:id,code,name',
            ]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $supplierProducts = $query->get()
            ->groupBy('product_id')
            ->map(function ($group) {
                $product = $group->first()->product;
                $suppliers = $group->map(function ($sp) {
                    return [
                        'supplier_code' => $sp->supplier->code,
                        'supplier_name' => $sp->supplier->name,
                        'supplier_sku' => $sp->supplier_sku,
                        'supplier_price' => $sp->supplier_price / 100,
                        'lead_time_days' => $sp->lead_time_days,
                        'minimum_order_qty' => $sp->minimum_order_quantity,
                        'is_preferred' => $sp->is_preferred,
                    ];
                })->toArray();

                // Find lowest price
                $lowestPrice = collect($suppliers)->min('supplier_price');
                $highestPrice = collect($suppliers)->max('supplier_price');

                return [
                    'product' => [
                        'uuid' => $product->uuid,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'current_cost_price' => $product->cost_price,
                    ],
                    'supplier_count' => count($suppliers),
                    'lowest_price' => $lowestPrice,
                    'highest_price' => $highestPrice,
                    'price_variance' => $highestPrice - $lowestPrice,
                    'suppliers' => $suppliers,
                ];
            })
            ->values();

        return [
            'product_id' => $productId,
            'data' => $supplierProducts->toArray(),
        ];
    }
}
