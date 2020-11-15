<?php

namespace App\Exports\Sales;

use App\Exports\Sales\Sheets\Invoices as Base;
use App\Exports\Sales\Sheets\InvoiceItems;
use App\Exports\Sales\Sheets\InvoiceItemTaxes;
use App\Exports\Sales\Sheets\InvoiceHistories;
use App\Exports\Sales\Sheets\InvoiceTotals;
use App\Exports\Sales\Sheets\InvoiceTransactions;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class Invoices implements WithMultipleSheets
{
    public $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function sheets(): array
    {
        return [
            'invoices' => new Base($this->ids),
            'invoice_items' => new InvoiceItems($this->ids),
            'invoice_item_taxes' => new InvoiceItemTaxes($this->ids),
            'invoice_histories' => new InvoiceHistories($this->ids),
            'invoice_totals' => new InvoiceTotals($this->ids),
            'invoice_transactions' => new InvoiceTransactions($this->ids),
        ];
    }
}
