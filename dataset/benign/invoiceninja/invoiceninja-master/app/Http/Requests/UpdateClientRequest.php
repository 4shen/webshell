<?php

namespace App\Http\Requests;

class UpdateClientRequest extends ClientRequest
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

        $rules = [];

        if ($this->user()->account->client_number_counter) {
            $rules['id_number'] = 'unique:clients,id_number,'.$this->entity()->id.',id,account_id,' . $this->user()->account_id;
        }

        return $rules;
    }
}
