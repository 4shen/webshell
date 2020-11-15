<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CachetHQ\Cachet\Console;

use CachetHQ\Cachet\Console\Commands\AppResetCommand;
use CachetHQ\Cachet\Console\Commands\AppUpdateCommand;
use CachetHQ\Cachet\Console\Commands\BeaconCommand;
use CachetHQ\Cachet\Console\Commands\DemoMetricPointSeederCommand;
use CachetHQ\Cachet\Console\Commands\DemoSeederCommand;
use CachetHQ\Cachet\Console\Commands\InstallCommand;
use CachetHQ\Cachet\Console\Commands\VersionCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * This is the console kernel class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 * @author Joseph Cohen <joe@alt-three.com>
 * @author James Brooks <james@alt-three.com>
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AppResetCommand::class,
        AppUpdateCommand::class,
        BeaconCommand::class,
        DemoMetricPointSeederCommand::class,
        DemoSeederCommand::class,
        InstallCommand::class,
        VersionCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cachet:beacon')->twiceDaily(0, 12);
    }
}
