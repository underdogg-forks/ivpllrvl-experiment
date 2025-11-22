<?php

namespace Modules\Invoices\Services;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoicesRecurring;
use RuntimeException;

/**
 * InvoicesRecurringService.
 */
class InvoicesRecurringService extends BaseService
{
    public function getValidationRules(): array
    {
        return [
            'invoice_id'       => 'required|integer',
            'recur_start_date' => 'required|date',
            'recur_end_date'   => 'nullable|date',
            'recur_frequency'  => 'required|string',
            'recur_next_date'  => 'nullable|date',
        ];
    }

    public function stopRecurring(int $recurringId): void
    {
        $this->update($recurringId, ['recur_status' => 0]);
    }

    /**
     * Get all recurring invoices with relationships.
     *
     * @param array $relations Relations to eager load
     * @param int   $perPage   Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(array $relations = ['invoice'], int $perPage = 15)
    {
        return InvoicesRecurring::query()->with($relations)
            ->orderBy('recur_start_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * @param $invoice_recurring_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function stop()
     */
    public function stop($invoice_recurring_id)
    {
        $now = Carbon::today()->toDateString();

        InvoicesRecurring::query()
            ->where('invoice_recurring_id', $invoice_recurring_id)
            ->update([
                'recur_end_date'  => $now,
                'recur_next_date' => null,
            ]);
    }

    /**
     * Sets filter to only recurring invoices which should be generated now.
     *
     * @return Mdl_Invoices_Recurring
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function active()
     */
    public function active()
    {
        $today = Carbon::today()->toDateString();

        return InvoicesRecurring::query()
            ->where('recur_status', 1)
            ->whereDate('recur_next_date', '<=', $today)
            ->where(function (Builder $q) use ($today) {
                $q->whereDate('recur_end_date', '>', $today)
                  ->orWhereNull('recur_end_date');
            });
    }

    /**
     * @param $invoice_recurring_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoices/models/Mdl_invoice_recurring.php
     *
     * @legacy-function set_next_recur_date()
     */
    public function set_next_recur_date($invoice_recurring_id)
    {
        $recurring = InvoicesRecurring::query()
            ->where('invoice_recurring_id', $invoice_recurring_id)
            ->first();

        if (! $recurring) {
            return;
        }

        $currentNext = $recurring->recur_next_date;
        if (empty($currentNext)) {
            throw new RuntimeException('Recurring entry has no next date set.');
        }

        $nextDate = $this->incrementDate((string) $currentNext, (string) $recurring->recur_frequency);

        InvoicesRecurring::query()
            ->where('invoice_recurring_id', $invoice_recurring_id)
            ->update(['recur_next_date' => $nextDate]);
    }

    private function incrementDate(string $date, string $frequency): string
    {
        $dt = Carbon::parse($date);
        $freq = strtolower(trim($frequency));

        return match (true) {
            $freq === 'daily' || $freq === 'day' => $dt->addDay()->toDateString(),
            $freq === 'weekly' || $freq === 'week' => $dt->addWeek()->toDateString(),
            $freq === 'fortnight' || $freq === 'fortnightly' || $freq === 'every 2 weeks' => $dt->addWeeks(2)->toDateString(),
            $freq === 'monthly' || $freq === 'month' => $dt->addMonth()->toDateString(),
            $freq === 'quarterly' || $freq === 'quarter' => $dt->addMonths(3)->toDateString(),
            $freq === 'biannually' || $freq === 'semiannually' || $freq === 'every 6 months' => $dt->addMonths(6)->toDateString(),
            $freq === 'yearly' || $freq === 'annual' || $freq === 'year' => $dt->addYear()->toDateString(),
            default => $this->tryFlexibleIncrement($dt, $frequency),
        };
    }

    private function tryFlexibleIncrement(Carbon $dt, string $frequency): string
    {
        $normalized = trim($frequency);

        if (preg_match('/^P\d+[DWMY]$/i', $normalized)) {
            try {
                $interval = new DateInterval($normalized);
                return $dt->add($interval)->toDateString();
            } catch (\Throwable $e) {
            }
        }

        try {
            $dt->add(DateInterval::createFromDateString($normalized));
            return $dt->toDateString();
        } catch (\Throwable $e) {
            throw new RuntimeException('Unsupported recur_frequency: ' . $frequency);
        }
    }

    protected function getModelClass(): string
    {
        return InvoicesRecurring::class;
    }
}
