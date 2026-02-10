<?php

namespace App\Repositories\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CreditTransactionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get by customer
     */
    public function getByCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get charge transactions
     */
    public function getCharges(?int $customerId = null): Collection;

    /**
     * Get payment transactions
     */
    public function getPayments(?int $customerId = null): Collection;

    /**
     * Get overdue invoices
     */
    public function getOverdue(): Collection;

    /**
     * Get aging report (4 buckets: current, 31-60, 61-90, over-90)
     */
    public function getAgingReport(): array;

    /**
     * Get payment allocation history for invoice
     */
    public function getAllocationHistory(int $saleId): Collection;

    /**
     * Get unpaid invoices for customer (FIFO order)
     */
    public function getUnpaidInvoices(int $customerId): Collection;

    /**
     * Get total outstanding for customer
     */
    public function getTotalOutstanding(int $customerId): int;

    /**
     * Get collection report (payments received)
     */
    public function getCollectionReport(Carbon $from, Carbon $to): array;
}
