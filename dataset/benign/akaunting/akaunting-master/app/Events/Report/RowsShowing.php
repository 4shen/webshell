<?php

namespace App\Events\Report;

use Illuminate\Queue\SerializesModels;

class RowsShowing
{
    use SerializesModels;

    public $class;

    /**
     * Create a new event instance.
     *
     * @param $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }
}
