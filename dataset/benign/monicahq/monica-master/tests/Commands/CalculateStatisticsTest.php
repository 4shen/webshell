<?php

namespace Tests\Commands;

use Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CalculateStatisticsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function the_command_runs_well()
    {
        $runsWell = true;

        try {
            $this->artisan('monica:calculatestatistics');
        } catch (QueryException $e) {
            $runsWell = false;
        }

        $this->assertTrue($runsWell);
    }
}
