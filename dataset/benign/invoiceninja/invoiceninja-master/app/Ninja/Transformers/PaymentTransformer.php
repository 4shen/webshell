<?php

namespace App\Ninja\Transformers;

use App\Models\Account;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;

/**
 * @SWG\Definition(definition="Payment", required={"invoice_id"}, @SWG\Xml(name="Payment"))
 */
class PaymentTransformer extends EntityTransformer
{
    /**
     * @SWG\Property(property="id", type="integer", example=1, readOnly=true)
     * @SWG\Property(property="amount", type="number", format="float", example=10, readOnly=true)
     * @SWG\Property(property="transaction_reference", type="string", example="Transaction Reference")
     * @SWG\Property(property="payment_date", type="string", format="date", example="2018-01-01")
     * @SWG\Property(property="updated_at", type="integer", example=1451160233, readOnly=true)
     * @SWG\Property(property="archived_at", type="integer", example=1451160233, readOnly=true)
     * @SWG\Property(property="is_deleted", type="boolean", example=false, readOnly=true)
     * @SWG\Property(property="payment_type_id", type="integer", example=1)
     * @SWG\Property(property="invoice_id", type="integer", example=1)
     * @SWG\Property(property="private_notes", type="string", example="Notes...")
     */
    protected $defaultIncludes = [];

    protected $availableIncludes = [
        'client',
        'invoice',
    ];

    public function __construct($account = null, $serializer = null, $invoice = null)
    {
        parent::__construct($account, $serializer);

        $this->invoice = $invoice;
    }

    public function includeInvoice(Payment $payment)
    {
        $transformer = new InvoiceTransformer($this->account, $this->serializer);

        return $this->includeItem($payment->invoice, $transformer, 'invoice');
    }

    public function includeClient(Payment $payment)
    {
        $transformer = new ClientTransformer($this->account, $this->serializer);

        return $this->includeItem($payment->client, $transformer, 'client');
    }

    public function transform(Payment $payment)
    {
        return array_merge($this->getDefaults($payment), [
            'id' => (int) $payment->public_id,
            'amount' => (float) $payment->amount,
            'transaction_reference' => $payment->transaction_reference ?: '',
            'payment_date' => $payment->payment_date ?: '',
            'updated_at' => $this->getTimestamp($payment->updated_at),
            'archived_at' => $this->getTimestamp($payment->deleted_at),
            'is_deleted' => (bool) $payment->is_deleted,
            'payment_type_id' => (int) ($payment->payment_type_id ?: 0),
            'invoice_id' => (int) ($this->invoice ? $this->invoice->public_id : $payment->invoice->public_id),
            'invoice_number' => $this->invoice ? $this->invoice->invoice_number : $payment->invoice->invoice_number,
            'private_notes' => $payment->private_notes ?: '',
            'exchange_rate' => (float) $payment->exchange_rate,
            'exchange_currency_id' => (int) $payment->exchange_currency_id,
            'refunded' => (float) $payment->refunded,
            'payment_status_id' => (int) ($payment->payment_status_id ?: PAYMENT_STATUS_COMPLETED),
        ]);
    }
}
