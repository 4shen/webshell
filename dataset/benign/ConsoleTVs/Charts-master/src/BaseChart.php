<?php

declare(strict_types=1);

namespace ConsoleTVs\Charts;

use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;

abstract class BaseChart
{
    /**
     * Determines the chart name to be used on the
     * route. If null, the name will be a snake_case
     * version of the class name.
     */
    public ?string $name;

    /**
     * Determines the name suffix of the chart route.
     * This will also be used to get the chart URL
     * from the blade directrive. If null, the chart
     * name will be used.
     */
    public ?string $routeName;

    /**
     * Determines the prefix that will be used by the chart
     * endpoint.
     */
    public ?string $prefix;

    /**
     * Determines the middlewares that will be applied
     * to the chart endpoint.
     */
    public ?array $middlewares;

    /**
     * Handles the HTTP request of the chart. This must always
     * return the chart instance. Do not return a string or an array.
     */
    abstract public function handler(Request $request): Chartisan;
}
