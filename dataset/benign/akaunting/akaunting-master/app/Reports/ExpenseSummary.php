<?php

namespace App\Reports;

use App\Abstracts\Report;
use App\Models\Banking\Transaction;
use App\Models\Purchase\Bill;
use App\Utilities\Recurring;

class ExpenseSummary extends Report
{
    public $default_name = 'reports.summary.expense';

    public $icon = 'fa fa-shopping-cart';

    public $chart = [
        'line' => [
            'width' => '0',
            'height' => '300',
            'options' => [
                'color' => '#ef3232',
                'legend' => [
                    'display' => false,
                ],
            ],
            'backgroundColor' => '#ef3232',
            'color' => '#ef3232',
        ],
    ];

    public function setData()
    {
        $transactions = $this->applyFilters(Transaction::with('recurring')->expense()->isNotTransfer(), ['date_field' => 'paid_at']);

        switch ($this->getSetting('basis')) {
            case 'cash':
                // Payments
                $payments = $transactions->get();
                $this->setTotals($payments, 'paid_at');

                break;
            default:
                // Bills
                $bills = $this->applyFilters(Bill::with('recurring', 'transactions')->accrued(), ['date_field' => 'billed_at'])->get();
                Recurring::reflect($bills, 'billed_at');
                $this->setTotals($bills, 'billed_at');

                // Payments
                $payments = $transactions->isNotDocument()->get();
                Recurring::reflect($payments, 'paid_at');
                $this->setTotals($payments, 'paid_at');

                break;
        }
    }
}
