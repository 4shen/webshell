<?php

namespace App\Events\Common;

use Illuminate\Queue\SerializesModels;

class CompanySwitched
{
    use SerializesModels;

    public $company;

    public $old_company_id;

    /**
     * Create a new event instance.
     *
     * @param $company
     * @param $old_company_id
     */
    public function __construct($company, $old_company_id)
    {
        $this->company = $company;
        $this->old_company_id = $old_company_id;
    }
}
