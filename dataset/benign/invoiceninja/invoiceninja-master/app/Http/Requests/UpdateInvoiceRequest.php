<?php

namespace App\Http\Requests;

use App\Models\Client;

class UpdateInvoiceRequest extends InvoiceRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->entity() && $this->user()->can('edit', $this->entity());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (! $this->entity()) {
            return [];
        }

        $invoiceId = $this->entity()->id;

        $rules = [
            'client' => 'required',
            'invoice_items' => 'valid_invoice_items',
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $invoiceId . ',id,account_id,' . $this->user()->account_id,
            'discount' => 'positive',
            'invoice_date' => 'required',
            //'due_date' => 'date',
            //'start_date' => 'date',
            //'end_date' => 'date',
        ];

        if ($this->user()->account->client_number_counter) {
            $clientId = Client::getPrivateId(request()->input('client')['public_id']);
            $rules['client.id_number'] = 'unique:clients,id_number,'.$clientId.',id,account_id,' . $this->user()->account_id;
        }

        /* There's a problem parsing the dates
        if (Request::get('is_recurring') && Request::get('start_date') && Request::get('end_date')) {
            $rules['end_date'] = 'after' . Request::get('start_date');
        }
        */

        return $rules;
    }
}
