<?php

namespace App\Exports\Purchases\Sheets;

use App\Abstracts\Export;
use App\Models\Purchase\BillHistory as Model;

class BillHistories extends Export
{
    public function collection()
    {
        $model = Model::with('bill')->usingSearchString(request('search'));

        if (!empty($this->ids)) {
            $model->whereIn('bill_id', (array) $this->ids);
        }

        return $model->cursor();
    }

    public function map($model): array
    {
        $bill = $model->bill;

        if (empty($bill)) {
            return [];
        }

        $model->bill_number = $bill->bill_number;

        return parent::map($model);
    }

    public function fields(): array
    {
        return [
            'bill_number',
            'status',
            'notify',
            'description',
        ];
    }
}
