<?php

namespace App\Http\Resources\ContactField;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @extends JsonResource<\App\Models\Contact\ContactFieldLabel>
 */
class ContactFieldLabel extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'object' => 'contactfieldlabel',
            'type' => $this->label_i18n ?: $this->label,
            'label' => $this->label_i18n ? trans('people.contact_field_label_'.$this->label_i18n) : $this->label,
            'account' => [
                'id' => $this->account_id,
            ],
            'created_at' => DateHelper::getTimestamp($this->created_at),
            'updated_at' => DateHelper::getTimestamp($this->updated_at),
        ];
    }
}
