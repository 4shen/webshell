<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Http\Controllers\Api;

use CachetHQ\Cachet\Bus\Commands\Metric\CreateMetricPointCommand;
use CachetHQ\Cachet\Bus\Commands\Metric\RemoveMetricPointCommand;
use CachetHQ\Cachet\Bus\Commands\Metric\UpdateMetricPointCommand;
use CachetHQ\Cachet\Models\Metric;
use CachetHQ\Cachet\Models\MetricPoint;
use GrahamCampbell\Binput\Facades\Binput;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MetricPointController extends AbstractApiController
{
    /**
     * Get a single metric point.
     *
     * @param \CachetHQ\Cachet\Models\Metric      $metric
     * @param \CachetHQ\Cachet\Models\MetricPoint $metricPoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Metric $metric, MetricPoint $metricPoint)
    {
        $points = $metric->points()->paginate(Binput::get('per_page', 20));

        return $this->paginator($points, Request::instance());
    }

    /**
     * Create a new metric point.
     *
     * @param \CachetHQ\Cachet\Models\Metric $metric
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Metric $metric)
    {
        try {
            $metricPoint = execute(new CreateMetricPointCommand(
                $metric,
                Binput::get('value'),
                Binput::get('timestamp')
            ));
        } catch (QueryException $e) {
            throw new BadRequestHttpException();
        }

        return $this->item($metricPoint);
    }

    /**
     * Updates a metric point.
     *
     * @param \CachetHQ\Cachet\Models\Metric      $metric
     * @param \CachetHQ\Cachet\Models\MetricPoint $metricPoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Metric $metric, MetricPoint $metricPoint)
    {
        $metricPoint = execute(new UpdateMetricPointCommand(
            $metricPoint,
            $metric,
            Binput::get('value'),
            Binput::get('timestamp')
        ));

        return $this->item($metricPoint);
    }

    /**
     * Destroys a metric point.
     *
     * @param \CachetHQ\Cachet\Models\Metric      $metric
     * @param \CachetHQ\Cachet\Models\MetricPoint $metricPoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Metric $metric, MetricPoint $metricPoint)
    {
        execute(new RemoveMetricPointCommand($metricPoint));

        return $this->noContent();
    }
}
