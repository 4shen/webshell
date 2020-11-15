<?php

namespace App\Ninja\Transformers;

use App\Models\Expense;

/**
 * @SWG\Definition(definition="Expense", @SWG\Xml(name="Expense"))
 */
class ExpenseTransformer extends EntityTransformer
{
    /**
     * @SWG\Property(property="id", type="integer", example=1, readOnly=true)
     * @SWG\Property(property="private_notes", type="string", example="Notes...")
     * @SWG\Property(property="public_notes", type="string", example="Notes...")
     * @SWG\Property(property="should_be_invoiced", type="boolean", example=false)
     * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
     * @SWG\Property(property="archived_at", type="integer", example=1451160233, readOnly=true)
     * @SWG\Property(property="transaction_id", type="integer", example=1)
     * @SWG\Property(property="transaction_reference", type="string", example="")
     * @SWG\Property(property="bank_id", type="integer", example=1)
     * @SWG\Property(property="expense_currency_id", type="integer", example=1)
     * @SWG\Property(property="expense_category_id", type="integer", example=1)
     * @SWG\Property(property="amount", type="number", format="float,", example="17.5")
     * @SWG\Property(property="expense_date", type="string", format="date", example="2016-01-01")
     * @SWG\Property(property="exchange_rate", type="number", format="float", example="")
     * @SWG\Property(property="invoice_currency_id", type="integer", example=1)
     * @SWG\Property(property="is_deleted", type="boolean", example=false)
     * @SWG\Property(property="tax_name1", type="string", example="VAT")
     * @SWG\Property(property="tax_name2", type="string", example="Upkeep")
     * @SWG\Property(property="tax_rate1", type="number", format="float", example="17.5")
     * @SWG\Property(property="tax_rate2", type="number", format="float", example="30.0")
     * @SWG\Property(property="client_id", type="integer", example=1)
     * @SWG\Property(property="invoice_id", type="integer", example=1)
     * @SWG\Property(property="vendor_id", type="integer", example=1)
     */

    protected $availableIncludes = [
        'documents',
    ];

    public function __construct($account = null, $serializer = null, $client = null)
    {
        parent::__construct($account, $serializer);

        $this->client = $client;
    }

    public function includeDocuments(Expense $expense)
    {
        $transformer = new DocumentTransformer($this->account, $this->serializer);

        $expense->documents->each(function ($document) use ($expense) {
            $document->setRelation('expense', $expense);
            $document->setRelation('invoice', $expense->invoice);
        });

        return $this->includeCollection($expense->documents, $transformer, ENTITY_DOCUMENT);
    }

    public function transform(Expense $expense)
    {
        return array_merge($this->getDefaults($expense), [
            'id' => (int) $expense->public_id,
            'private_notes' => $expense->private_notes,
            'public_notes' => $expense->public_notes,
            'should_be_invoiced' => (bool) $expense->should_be_invoiced,
            'updated_at' => $this->getTimestamp($expense->updated_at),
            'archived_at' => $this->getTimestamp($expense->deleted_at),
            'transaction_id' => $expense->transaction_id ?: '',
            'transaction_reference' => $expense->transaction_reference ?: '',
            'bank_id' => (int) ($expense->bank_id ?: 0),
            'expense_currency_id' => (int) ($expense->expense_currency_id ?: 0),
            'expense_category_id' => (int) ($expense->expense_category ? $expense->expense_category->public_id : 0),
            'amount' => (float) $expense->amount,
            'expense_date' => $expense->expense_date ?: '',
            'payment_date' => $expense->payment_date ?: '',
            'invoice_documents' => (bool) $expense->invoice_documents,
            'payment_type_id' => (int) $expense->payment_type_id,
            'exchange_rate' => (float) $expense->exchange_rate,
            'invoice_currency_id' => (int) $expense->invoice_currency_id,
            'is_deleted' => (bool) $expense->is_deleted,
            'tax_name1' => $expense->tax_name1 ?: '',
            'tax_name2' => $expense->tax_name2 ?: '',
            'tax_rate1' => (float) ($expense->tax_rate1 ?: 0),
            'tax_rate2' => (float) ($expense->tax_rate2 ?: 0),
            'client_id' => (int) ($this->client ? $this->client->public_id : (isset($expense->client->public_id) ? $expense->client->public_id : 0)),
            'invoice_id' => (int) (isset($expense->invoice->public_id) ? $expense->invoice->public_id : 0),
            'vendor_id' => (int) (isset($expense->vendor->public_id) ? $expense->vendor->public_id : 0),
            'custom_value1' => $expense->custom_value1 ?: '',
            'custom_value2' => $expense->custom_value2 ?: '',
        ]);
    }
}
