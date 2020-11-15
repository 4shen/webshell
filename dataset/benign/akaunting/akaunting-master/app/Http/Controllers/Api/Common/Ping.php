<?php

namespace App\Http\Controllers\Api\Common;

use App\Abstracts\Http\ApiController;
use Date;
use Dingo\Api\Routing\Helpers;

class Ping extends ApiController
{
    use Helpers;

    /**
     * Responds with a status for heath check.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->response->array([
            'status' => 'ok',
            'timestamp' => Date::now(),
        ]);
    }
}
