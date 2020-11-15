<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Commands\Metric;

use CachetHQ\Cachet\Models\Metric;
use CachetHQ\Cachet\Models\MetricPoint;

/**
 * This is the update metric point command.
 *
 * @author James Brooks <james@alt-three.com>
 */
final class UpdateMetricPointCommand
{
    /**
     * The metric point.
     *
     * @var \CachetHQ\Cachet\Models\MetricPoint
     */
    public $point;

    /**
     * The metric.
     *
     * @var \CachetHQ\Cachet\Models\Metric
     */
    public $metric;

    /**
     * The metric point value.
     *
     * @var float
     */
    public $value;

    /**
     * The metric point created at.
     *
     * @var string
     */
    public $created_at;

    /**
     * The validation rules.
     *
     * @var string[]
     */
    public $rules = [
        'value'      => 'nullable|numeric',
        'created_at' => 'nullable|string',
    ];

    /**
     * Create a new update metric point command instance.
     *
     * @param \CachetHQ\Cachet\Models\MetricPoint $point
     * @param \CachetHQ\Cachet\Models\Metric      $metric
     * @param float                               $value
     * @param string                              $created_at
     *
     * @return void
     */
    public function __construct(MetricPoint $point, Metric $metric, $value, $created_at)
    {
        $this->point = $point;
        $this->metric = $metric;
        $this->value = $value;
        $this->created_at = $created_at;
    }
}
