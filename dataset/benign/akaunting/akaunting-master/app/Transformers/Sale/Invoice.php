<?php

namespace App\Transformers\Sale;

use App\Transformers\Banking\Transaction;
use App\Transformers\Common\Contact;
use App\Transformers\Setting\Currency;
use App\Models\Sale\Invoice as Model;
use League\Fractal\TransformerAbstract;

class Invoice extends TransformerAbstract
{
    /**
     * @var array
     */
    protected $defaultIncludes = ['contact', 'currency', 'histories', 'items', 'transactions'];

    /**
     * @param Model $model
     * @return array
     */
    public function transform(Model $model)
    {
        return [
            'id' => $model->id,
            'company_id' => $model->company_id,
            'invoice_number' => $model->invoice_number,
            'order_number' => $model->order_number,
            'status' => $model->status,
            'invoiced_at' => $model->invoiced_at ? $model->invoiced_at->toIso8601String() : '',
            'due_at' => $model->due_at ? $model->due_at->toIso8601String() : '',
            'amount' => $model->amount,
            'currency_code' => $model->currency_code,
            'currency_rate' => $model->currency_rate,
            'contact_id' => $model->contact_id,
            'contact_name' => $model->contact_name,
            'contact_email' => $model->contact_email,
            'contact_tax_number' => $model->contact_tax_number,
            'contact_phone' => $model->contact_phone,
            'contact_address' => $model->contact_address,
            'notes' => $model->notes,
            'attachment' => $model->attachment,
            'created_at' => $model->created_at ? $model->created_at->toIso8601String() : '',
            'updated_at' => $model->updated_at ? $model->updated_at->toIso8601String() : '',
        ];
    }

    /**
     * @param Model $model
     * @return \League\Fractal\Resource\Item
     */
    public function includeContact(Model $model)
    {
        return $this->item($model->contact, new Contact());
    }

    /**
     * @param  Model $model
     * @return \League\Fractal\Resource\Item
     */
    public function includeCurrency(Model $model)
    {
        return $this->item($model->currency, new Currency());
    }

    /**
     * @param Model $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeHistories(Model $model)
    {
        return $this->collection($model->histories, new InvoiceHistories());
    }

    /**
     * @param Model $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeItems(Model $model)
    {
        return $this->collection($model->items, new InvoiceItems());
    }

    /**
     * @param Model $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeTransactions(Model $model)
    {
        return $this->collection($model->transactions, new Transaction());
    }
}
