<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\CreditService;
use App\Services\InventoryService;
use App\Http\Resources\SaleResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CreditAgingResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    protected DashboardService $dashboardService;
    protected CreditService $creditService;
    protected InventoryService $inventoryService;

    public function __construct(
        DashboardService $dashboardService,
        CreditService $creditService,
        InventoryService $inventoryService
    ) {
        $this->dashboardService = $dashboardService;
        $this->creditService = $creditService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get today's summary statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $summary = $this->dashboardService->getTodaySummary($store);

        return $this->successResponse($summary);
    }

    /**
     * Get sales trend data for line chart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function salesTrend(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $days = $request->input('days', 30);

        // Validate days parameter
        if ($days < 1 || $days > 365) {
            return $this->errorResponse('Days parameter must be between 1 and 365', 422);
        }

        $trend = $this->dashboardService->getSalesTrend($store, $days);

        return $this->successResponse([
            'period' => "{$days} days",
            'data' => $trend,
        ]);
    }

    /**
     * Get top selling products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function topProducts(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $limit = $request->input('limit', 10);
        $days = $request->input('days', 30);

        // Validate parameters
        if ($limit < 1 || $limit > 50) {
            return $this->errorResponse('Limit parameter must be between 1 and 50', 422);
        }

        if ($days < 1 || $days > 365) {
            return $this->errorResponse('Days parameter must be between 1 and 365', 422);
        }

        $products = $this->dashboardService->getTopProducts($store, $limit, $days);

        return $this->successResponse([
            'period' => "{$days} days",
            'data' => $products,
        ]);
    }

    /**
     * Get sales breakdown by category for pie chart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function salesByCategory(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $days = $request->input('days', 30);

        if ($days < 1 || $days > 365) {
            return $this->errorResponse('Days parameter must be between 1 and 365', 422);
        }

        $categories = $this->dashboardService->getSalesByCategory($store, $days);

        // Calculate total and percentages
        $total = array_sum(array_column($categories, 'total'));

        $data = array_map(function ($category) use ($total) {
            $category['percentage'] = $total > 0 ? round(($category['total'] / $total) * 100, 2) : 0;
            return $category;
        }, $categories);

        return $this->successResponse([
            'period' => "{$days} days",
            'total' => $total,
            'total_pesos' => $total / 100,
            'data' => $data,
        ]);
    }

    /**
     * Get credit aging summary.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function creditAging(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $agingReport = $this->creditService->getAgingReport($store);

        return $this->successResponse([
            'summary' => $agingReport['summary'],
            'customers' => CreditAgingResource::collection($agingReport['customers']),
        ]);
    }

    /**
     * Get recent transactions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recentTransactions(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $limit = $request->input('limit', 10);

        if ($limit < 1 || $limit > 50) {
            return $this->errorResponse('Limit parameter must be between 1 and 50', 422);
        }

        $transactions = $this->dashboardService->getRecentTransactions($store, $limit);

        return $this->successResponse([
            'data' => SaleResource::collection($transactions),
        ]);
    }

    /**
     * Get stock alerts (products below reorder point).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stockAlerts(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $products = $this->dashboardService->getStockAlerts($store);

        return $this->successResponse([
            'count' => $products->count(),
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Get upcoming deliveries this week.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upcomingDeliveries(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $deliveries = $this->dashboardService->getUpcomingDeliveries($store);

        return $this->successResponse([
            'count' => $deliveries->count(),
            'data' => $deliveries->map(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'uuid' => $delivery->uuid,
                    'delivery_number' => $delivery->delivery_number,
                    'sale_number' => $delivery->sale->sale_number ?? null,
                    'customer' => $delivery->customer ? [
                        'uuid' => $delivery->customer->uuid,
                        'name' => $delivery->customer->name,
                    ] : null,
                    'scheduled_date' => $delivery->scheduled_date->format('Y-m-d H:i:s'),
                    'status' => $delivery->status,
                    'delivery_address' => $delivery->delivery_address,
                ];
            }),
        ]);
    }

    /**
     * Get top customers by purchase amount.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function topCustomers(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $limit = $request->input('limit', 5);
        $days = $request->input('days', 30);

        if ($limit < 1 || $limit > 20) {
            return $this->errorResponse('Limit parameter must be between 1 and 20', 422);
        }

        if ($days < 1 || $days > 365) {
            return $this->errorResponse('Days parameter must be between 1 and 365', 422);
        }

        $customers = $this->dashboardService->getTopCustomers($store, $limit, $days);

        return $this->successResponse([
            'period' => "{$days} days",
            'data' => $customers,
        ]);
    }

    /**
     * Get comprehensive dashboard data in one request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function comprehensive(Request $request): JsonResponse
    {
        $store = $request->user()->store;
        $stats = $this->dashboardService->getComprehensiveStats($store);

        return $this->successResponse($stats);
    }
}
