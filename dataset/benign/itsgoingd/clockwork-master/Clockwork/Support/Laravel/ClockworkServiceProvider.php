<?php namespace Clockwork\Support\Laravel;

use Clockwork\Clockwork;
use Clockwork\Authentication\AuthenticatorInterface;
use Clockwork\DataSource\EloquentDataSource;
use Clockwork\DataSource\LaravelDataSource;
use Clockwork\DataSource\LaravelCacheDataSource;
use Clockwork\DataSource\LaravelEventsDataSource;
use Clockwork\DataSource\LaravelRedisDataSource;
use Clockwork\DataSource\LaravelQueueDataSource;
use Clockwork\DataSource\LaravelTwigDataSource;
use Clockwork\DataSource\LaravelViewsDataSource;
use Clockwork\DataSource\PhpDataSource;
use Clockwork\DataSource\SwiftDataSource;
use Clockwork\DataSource\TwigDataSource;
use Clockwork\DataSource\XdebugDataSource;
use Clockwork\Helpers\StackFilter;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork\Storage\StorageInterface;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\ServiceProvider;

class ClockworkServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if ($this->app['clockwork.support']->isCollectingData()) {
			$this->listenToEvents();
			$this->registerMiddleware();
		}

		// If Clockwork is disabled, return before registering middleware or routes
		if (! $this->app['clockwork.support']->isEnabled()) return;

		$this->registerRoutes();

		// register the Clockwork Web UI routes
		if ($this->app['clockwork.support']->isWebEnabled()) {
			$this->registerWebRoutes();
		}
	}

	protected function listenToEvents()
	{
		$support = $this->app['clockwork.support'];

		$this->listenToFrameworkEvents();

		if ($support->isFeatureEnabled('cache')) $this->app['clockwork.cache']->listenToEvents();
		if ($support->isFeatureEnabled('database')) $this->app['clockwork.eloquent']->listenToEvents();
		if ($support->isFeatureEnabled('events')) $this->app['clockwork.events']->listenToEvents();
		if ($support->isFeatureEnabled('queue')) {
			$this->app['clockwork.queue']->listenToEvents();
			$this->app['clockwork.queue']->setCurrentRequestId($this->app['clockwork.request']->id);
		}
		if ($support->isFeatureEnabled('redis')) {
			$this->app[RedisManager::class]->enableEvents();
			$this->app['clockwork.redis']->listenToEvents();
		}
		if ($support->isFeatureEnabled('views')) {
			$support->getConfig('features.views.use_twig_profiler', false)
				? $this->app['clockwork.twig']->listenToEvents() : $this->app['clockwork.views']->listenToEvents();
		}

		if ($support->isCollectingCommands()) $support->collectCommands();
		if ($support->isCollectingQueueJobs()) $support->collectQueueJobs();
	}

	protected function listenToFrameworkEvents()
	{
		$this->app['clockwork.laravel']->listenToEvents();
	}

	public function register()
	{
		$this->publishes([ __DIR__ . '/config/clockwork.php' => config_path('clockwork.php') ]);
		$this->mergeConfigFrom(__DIR__ . '/config/clockwork.php', 'clockwork');

		$this->app->singleton('clockwork', function ($app) {
			$support = $app['clockwork.support'];

			$clockwork = (new Clockwork)
				->setAuthenticator($app['clockwork.authenticator'])
				->setLog($app['clockwork.log'])
				->setRequest($app['clockwork.request'])
				->setStorage($app['clockwork.storage'])
				->addDataSource(new PhpDataSource())
				->addDataSource($app['clockwork.laravel']);

			if ($support->isFeatureEnabled('database')) $clockwork->addDataSource($app['clockwork.eloquent']);
			if ($support->isFeatureEnabled('cache')) $clockwork->addDataSource($app['clockwork.cache']);
			if ($support->isFeatureEnabled('redis')) $clockwork->addDataSource($app['clockwork.redis']);
			if ($support->isFeatureEnabled('queue')) $clockwork->addDataSource($app['clockwork.queue']);
			if ($support->isFeatureEnabled('events')) $clockwork->addDataSource($app['clockwork.events']);
			if ($support->isFeatureEnabled('emails')) $clockwork->addDataSource($app['clockwork.swift']);
			if ($support->isFeatureAvailable('xdebug')) $clockwork->addDataSource($app['clockwork.xdebug']);
			if ($support->isFeatureEnabled('views')) {
				$clockwork->addDataSource(
					$support->getConfig('features.views.use_twig_profiler', false) ? $app['clockwork.twig'] : $app['clockwork.views']
				);
			}

			return $clockwork;
		});

		$this->app->singleton('clockwork.authenticator', function ($app) {
			return $app['clockwork.support']->getAuthenticator();
		});

		$this->app->singleton('clockwork.log', function ($app) {
			return new Log;
		});

		$this->app->singleton('clockwork.request', function ($app) {
			return new Request;
		});

		$this->app->singleton('clockwork.storage', function ($app) {
			return $app['clockwork.support']->getStorage();
		});

		$this->app->singleton('clockwork.support', function ($app) {
			return new ClockworkSupport($app);
		});

		$this->registerCommands();
		$this->registerDataSources();
		$this->registerAliases();

		$this->app->make('clockwork.request'); // instantiate the request to have id and time available as early as possible
		$this->app['clockwork.support']->configureSerializer();
		$this->app['clockwork.laravel']->listenToEarlyEvents();

		if ($this->app['clockwork.support']->getConfig('register_helpers', true)) {
			require __DIR__ . '/helpers.php';
		}
	}

	// Register the artisan commands
	protected function registerCommands()
	{
		$this->commands([
			ClockworkCleanCommand::class
		]);
	}

	// Register Clockwork data sources
	protected function registerDataSources()
	{
		$this->app->singleton('clockwork.cache', function ($app) {
			return (new LaravelCacheDataSource(
				$app['events'],
				$app['clockwork.support']->getConfig('features.cache.collect_queries')
			));
		});

		$this->app->singleton('clockwork.eloquent', function ($app) {
			$dataSource = (new EloquentDataSource(
				$app['db'],
				$app['events'],
				$app['clockwork.support']->getConfig('features.database.collect_queries'),
				$app['clockwork.support']->getConfig('features.database.slow_threshold'),
				$app['clockwork.support']->getConfig('features.database.slow_only'),
				$app['clockwork.support']->getConfig('features.database.detect_duplicate_queries')
			));

			// if we are collecting queue jobs, filter out queries caused by the database queue implementation
			if ($app['clockwork.support']->isCollectingQueueJobs()) {
				$dataSource->addFilter(function ($query, $trace) {
					return ! $trace->first(StackFilter::make()->isClass(\Illuminate\Queue\Worker::class));
				}, 'early');
			}

			if ($app->runningUnitTests()) {
				$dataSource->addFilter(function ($query, $trace) {
					return ! $trace->first(StackFilter::make()->isClass([
						\Illuminate\Database\Migrations\Migrator::class,
						\Illuminate\Database\Console\Migrations\MigrateCommand::class
					]));
				});
			}

			return $dataSource;
		});

		$this->app->singleton('clockwork.events', function ($app) {
			return (new LaravelEventsDataSource(
				$app['events'],
				$app['clockwork.support']->getConfig('features.events.ignored_events', [])
			));
		});

		$this->app->singleton('clockwork.laravel', function ($app) {
			return (new LaravelDataSource(
				$app,
				$app['clockwork.support']->isFeatureEnabled('log'),
				$app['clockwork.support']->isFeatureEnabled('routes')
			))
				->setLog($app['clockwork.log']);
		});

		$this->app->singleton('clockwork.queue', function ($app) {
			return (new LaravelQueueDataSource($app['queue']->connection()));
		});

		$this->app->singleton('clockwork.redis', function ($app) {
			$dataSource = new LaravelRedisDataSource($app['events']);

			// if we are collecting queue jobs, filter out commands executed by the redis queue implementation
			if ($app['clockwork.support']->isCollectingQueueJobs()) {
				$dataSource->addFilter(function ($query, $trace) {
					return ! $trace->first(StackFilter::make()->isClass([
						\Illuminate\Queue\RedisQueue::class,
						\Laravel\Horizon\Repositories\RedisJobRepository::class,
						\Laravel\Horizon\Repositories\RedisTagRepository::class,
						\Laravel\Horizon\Repositories\RedisMetricsRepository::class
					]));
				});
			}

			return $dataSource;
		});

		$this->app->singleton('clockwork.swift', function ($app) {
			return new SwiftDataSource($app['mailer']->getSwiftMailer());
		});

		$this->app->singleton('clockwork.twig', function ($app) {
			return new TwigDataSource($app['twig']);
		});

		$this->app->singleton('clockwork.views', function ($app) {
			return new LaravelViewsDataSource(
				$app['events'],
				$app['clockwork.support']->getConfig('features.views.collect_data')
			);
		});

		$this->app->singleton('clockwork.xdebug', function ($app) {
			return new XdebugDataSource;
		});
	}

	// Set up aliases for all Clockwork parts so they can be resolved by type-hinting
	protected function registerAliases()
	{
		$this->app->alias('clockwork', Clockwork::class);

		$this->app->alias('clockwork.authenticator', AuthenticatorInterface::class);
		$this->app->alias('clockwork.log', Log::class);
		$this->app->alias('clockwork.storage', StorageInterface::class);
		$this->app->alias('clockwork.support', ClockworkSupport::class);

		$this->app->alias('clockwork.cache', LaravelCacheDataSource::class);
		$this->app->alias('clockwork.eloquent', EloquentDataSource::class);
		$this->app->alias('clockwork.events', LaravelEventsDataSource::class);
		$this->app->alias('clockwork.laravel', LaravelDataSource::class);
		$this->app->alias('clockwork.queue', LaravelQueueDataSource::class);
		$this->app->alias('clockwork.redis', LaravelRedisDataSource::class);
		$this->app->alias('clockwork.swift', SwiftDataSource::class);
		$this->app->alias('clockwork.xdebug', XdebugDataSource::class);
	}

	// Register middleware
	protected function registerMiddleware()
	{
		$this->app[\Illuminate\Contracts\Http\Kernel::class]
			->prependMiddleware(ClockworkMiddleware::class);
	}

	protected function registerRoutes()
	{
		$this->app['router']->get('/__clockwork/{id}/extended', 'Clockwork\Support\Laravel\ClockworkController@getExtendedData')
			->where('id', '([0-9-]+|latest)');
		$this->app['router']->get('/__clockwork/{id}/{direction?}/{count?}', 'Clockwork\Support\Laravel\ClockworkController@getData')
			->where('id', '([0-9-]+|latest)')->where('direction', '(next|previous)')->where('count', '\d+');
	}

	protected function registerWebRoutes()
	{
		$this->app['router']->get('/__clockwork', 'Clockwork\Support\Laravel\ClockworkController@webRedirect');
		$this->app['router']->get('/__clockwork/app', 'Clockwork\Support\Laravel\ClockworkController@webIndex');
		$this->app['router']->get('/__clockwork/{path}', 'Clockwork\Support\Laravel\ClockworkController@webAsset')
			->where('path', '.+');
		$this->app['router']->post('/__clockwork/auth', 'Clockwork\Support\Laravel\ClockworkController@authenticate');
	}

	public function provides()
	{
		return [ 'clockwork' ];
	}
}
