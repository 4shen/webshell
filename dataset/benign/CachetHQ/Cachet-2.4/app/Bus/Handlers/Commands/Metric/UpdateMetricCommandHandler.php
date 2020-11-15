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

use CachetHQ\Cachet\Bus\Commands\Metric\UpdateMetricCommand;
use CachetHQ\Cachet\Bus\Events\Metric\MetricWasUpdatedEvent;
use CachetHQ\Cachet\Models\Metric;
use Illuminate\Contracts\Auth\Guard;

class UpdateMetricCommandHandler
{
    /**
     * The authentication guard instance.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new update metric command handler instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle the update metric command.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Metric\UpdateMetricCommand $command
     *
     * @return \CachetHQ\Cachet\Models\Metric
     */
    public function handle(UpdateMetricCommand $command)
    {
        $metric = $command->metric;

        $metric->update($this->filter($command));

        event(new MetricWasUpdatedEvent($this->auth->user(), $metric));

        return $metric;
    }

    /**
     * Filter the command data.
     *
     * @param \CachetHQ\Cachet\Bus\Commands\Metric\UpdateMetricCommand $command
     *
     * @return array
     */
    protected function filter(UpdateMetricCommand $command)
    {
        $params = [
            'name'          => $command->name,
            'suffix'        => $command->suffix,
            'description'   => $command->description,
            'default_value' => $command->default_value,
            'calc_type'     => $command->calc_type,
            'display_chart' => $command->display_chart,
            'places'        => $command->places,
            'default_view'  => $command->default_view,
            'threshold'     => $command->threshold,
            'order'         => $command->order,
            'visible'       => $command->visible,
        ];

        return array_filter($params, function ($val) {
            return $val !== null;
        });
    }
}
