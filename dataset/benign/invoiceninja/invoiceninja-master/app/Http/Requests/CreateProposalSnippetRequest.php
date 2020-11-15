<?php

namespace App\Http\Requests;

class CreateProposalSnippetRequest extends ProposalSnippetRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', ENTITY_PROPOSAL_SNIPPET);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => sprintf('required|unique:proposal_snippets,name,,id,account_id,%s', $this->user()->account_id),
        ];
    }
}
