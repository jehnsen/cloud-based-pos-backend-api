<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get today's summary statistics.
     *
     * @param Store $store
     * @return array
     */
    public function getTodaySummary(Store $store): array
    {
        $today = Carbon::today();

        return Cache::remember("dashboard_summary_{$store->id}_" . $today->format('Y-m-d'), 300, function () use ($store, $today) {
            // Today's sales
            $salesData = Sale::where('store_id', $store->id)
                ->whereDate('created_at', $today)
                ->where('status', '!=', 'voided')
                ->selectRaw('COUNT(*) as transaction_count, SUM(total_amount) as total_sales')
                ->first();

            // Low stock products count
            $lowStockCount = Product::where('store_id', $store->id)
                ->where('is_active', true)
                ->where('track_inventory', true)
                ->whereColumn('current_stock', '<=', 'reorder_point')
                ->count();

            // Outstanding credit
            $outstandingCredit = Customer::where('store_id', $store->id)
                ->sum('total_outstanding');

            // Pending deliveries
            $pendingDeliveries = Delivery::where('store_id', $store->id)
                ->whereIn('status', ['preparing', 'dispatched', 'in_transit'])
                ->count();

            return [
                'today_sales' => [
                    'amount' => (int) ($salesData->total_sales ?? 0),
                    'amount_pesos' => ($salesData->total_sales ?? 0) / 100,
                    'transaction_count' => (int) ($salesData->transaction_count ?? 0),
                ],
                'low_stock_count' => $lowStockCount,
                'outstanding_credit' => [
                    'amount' => (int) $outstandingCredit,
                    'amount_pesos' => $outstandingCredit / 100,
                ],
                'pending_deliveries' => $pendingDeliveries,
            ];
        });
    }

    /**
     * Get sales trend data for the last N days.
     *
     * @param Store $store
     * @param int $days
     * @return array
     */
    public function getSalesTrend(Store $store, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        return Cache::remember("sales_trend_{$store->id}_{$days}_" . Carbon::today()->format('Y-m-d'), 900, function () use ($store, $startDate) {
            $sales = Sale::where('store_id', $store->id)
                ->whereDate('created_at', '>=', $startDate)
                ->where('status', '!=', 'voided')
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as transactions')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return $sales->map(function ($sale) {
                return [
                    'date' => $sale->date,
                    'total' => (int) $sale->total,
                    'total_pesos' => $sale->total / 100,
                    'transactions' => (int) $sale->transactions,
                ];
            })->toArray();
        });
    }

    /**
     * Get top selling products.
     *
     * @param Store $store
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getTopProducts(Store $store, int $limit = 10, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        return Cache::remember("top_products_{$store->id}_{$limit}_{$days}_" . Carbon::today()->format('Y-m-d'), 900, function () use ($store, $startDate, $limit) {
            return DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.store_id', $store->id)
                ->whereDate('sales.created_at', '>=', $startDate)
                ->where('sales.status', '!=', 'voided')
                ->select(
                    'products.uuid',
                    'products.name',
                    'products.sku',
                    DB::raw('SUM(sale_items.quantity) as total_quantity'),
                    DB::raw('SUM(sale_items.line_total) as total_revenue')
                )
                ->groupBy('products.id', 'products.uuid', 'products.name', 'products.sku')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'uuid' => $product->uuid,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'total_quantity' => (float) $product->total_quantity,
                        'total_revenue' => (int) $product->total_revenue,
                        'total_revenue_pesos' => $product->total_revenue / 100,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get sales by category for pie chart.
     *
     * @param Store $store
     * @param int $days
     * @return array
     */
    public function getSalesByCategory(Store $store, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        return Cache::remember("sales_by_category_{$store->id}_{$days}_" . Carbon::today()->format('Y-m-d'), 900, function () use ($store, $startDate) {
            return DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('sales.store_id', $store->id)
                ->whereDate('sales.created_at', '>=', $startDate)
                ->where('sales.status', '!=', 'voided')
                ->select(
                    'categories.name',
                    'categories.slug',
                    DB::raw('SUM(sale_items.line_total) as total')
                )
                ->groupBy('categories.id', 'categories.name', 'categories.slug')
                ->orderByDesc('total')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'total' => (int) $category->total,
                        'total_pesos' => $category->total / 100,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get recent transactions.
     *
     * @param Store $store
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentTransactions(Store $store, int $limit = 10)
    {
        return Sale::where('store_id', $store->id)
            ->with(['customer:uuid,name', 'user:id,name', 'branch:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get stock alerts (products below reorder point).
     *
     * @param Store $store
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockAlerts(Store $store)
    {
        return Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->whereColumn('current_stock', '<=', 'reorder_point')
            ->with(['category:id,name', 'unit:id,name,abbreviation'])
            ->orderBy('current_stock', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * Get upcoming deliveries this week.
     *
     * @param Store $store
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingDeliveries(Store $store)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return Delivery::where('store_id', $store->id)
            ->whereBetween('scheduled_date', [$startOfWeek, $endOfWeek])
            ->whereIn('status', ['preparing', 'dispatched', 'in_transit'])
            ->with(['sale:id,uuid,sale_number', 'customer:id,uuid,name'])
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get();
    }

    /**
     * Get top customers by purchase amount.
     *
     * @param Store $store
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getTopCustomers(Store $store, int $limit = 5, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        return Cache::remember("top_customers_{$store->id}_{$limit}_{$days}_" . Carbon::today()->format('Y-m-d'), 900, function () use ($store, $startDate, $limit) {
            return DB::table('sales')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->where('sales.store_id', $store->id)
                ->whereDate('sales.created_at', '>=', $startDate)
                ->where('sales.status', '!=', 'voided')
                ->whereNotNull('sales.customer_id')
                ->select(
                    'customers.uuid',
                    'customers.name',
                    'customers.type',
                    DB::raw('COUNT(sales.id) as transaction_count'),
                    DB::raw('SUM(sales.total_amount) as total_purchases')
                )
                ->groupBy('customers.id', 'customers.uuid', 'customers.name', 'customers.type')
                ->orderByDesc('total_purchases')
                ->limit($limit)
                ->get()
                ->map(function ($customer) {
                    return [
                        'uuid' => $customer->uuid,
                        'name' => $customer->name,
                        'type' => $customer->type,
                        'transaction_count' => (int) $customer->transaction_count,
                        'total_purchases' => (int) $customer->total_purchases,
                        'total_purchases_pesos' => $customer->total_purchases / 100,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get comprehensive dashboard statistics.
     *
     * @param Store $store
     * @return array
     */
    public function getComprehensiveStats(Store $store): array
    {
        return [
            'summary' => $this->getTodaySummary($store),
            'sales_trend' => $this->getSalesTrend($store, 7), // Last 7 days for quick view
            'top_products' => $this->getTopProducts($store, 5),
            'sales_by_category' => $this->getSalesByCategory($store, 30),
            'recent_transactions' => $this->getRecentTransactions($store, 5),
            'stock_alerts_count' => $this->getStockAlerts($store)->count(),
            'upcoming_deliveries_count' => $this->getUpcomingDeliveries($store)->count(),
            'top_customers' => $this->getTopCustomers($store, 3),
        ];
    }
}
