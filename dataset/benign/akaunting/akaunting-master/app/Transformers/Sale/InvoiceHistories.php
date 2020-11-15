<?php

namespace App\Transformers\Sale;

use App\Models\Sale\InvoiceHistory as Model;
use League\Fractal\TransformerAbstract;

class InvoiceHistories extends TransformerAbstract
{
    /**
     * @param Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'invoice_id' => $model->invoice_id,
            'status' => $model->status,
            'notify' => $model->notify,
            'description' => $model->description,
            'created_at' => $model->created_at ? $model->created_at->toIso8601String() : '',
            'updated_at' => $model->updated_at ? $model->updated_at->toIso8601String() : '',
        ];
    }
}
