<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Bus\Handlers\Commands\Metric;

use CachetHQ\Cachet\Bus\Commands\Metric\UpdateMetricPointCommand;
use CachetHQ\Cachet\Bus\Events\Metric\MetricPointWasUpdatedEvent;
use CachetHQ\Cachet\Services\Dates\DateFactory;
use Illuminate\Contracts\Auth\Guard;

class UpdateMetricPointCommandHandler
{
    /**
     * The authentication guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * The date factory instance.
     *
     * @var \CachetHQ\Cachet\Services\Dates\DateFactory
     */
    protected $dates;

    /**
     * Create a new update metric point command handler instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard            $auth
     * @param \CachetHQ\Cachet\Services\Dates\DateFactory $dates
     *
     * @return void
     */
    public function __construct(Guard $auth, DateFactory $dates)
    {
        $this->auth = $auth;
        $this->dates = $dates;
    }

    /**
     * Handle the update metric point command.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Metric\UpdateMetricPointCommand $command
     *
     * @return \CachetHQ\Cachet\Models\MetricPoint
     */
    public function handle(UpdateMetricPointCommand $command)
    {
        $point = $command->point;
        $metric = $command->metric;
        $createdAt = $command->created_at;

        $data = [
            'metric_id' => $metric->id,
            'value'     => (float) $command->value,
        ];

        if ($createdAt) {
            $data['created_at'] = $this->dates->create('U', $createdAt)->format('Y-m-d H:i:s');
        }

        $point->update($data);

        event(new MetricPointWasUpdatedEvent($this->auth->user(), $point));

        return $point;
    }
}
