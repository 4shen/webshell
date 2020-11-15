<?php

declare(strict_types=1);

/**
 * @copyright 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OC\AppFramework\Bootstrap;

use Closure;
use OC\Search\SearchComposer;
use OC\Support\CrashReport\Registry;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\ILogger;
use Throwable;

class RegistrationContext {

	/** @var array[] */
	private $capabilities = [];

	/** @var array[] */
	private $crashReporters = [];

	/** @var array[] */
	private $services = [];

	/** @var array[] */
	private $aliases = [];

	/** @var array[] */
	private $parameters = [];

	/** @var array[] */
	private $eventListeners = [];

	/** @var array[] */
	private $middlewares = [];

	/** @var array[] */
	private $searchProviders = [];

	/** @var ILogger */
	private $logger;

	public function __construct(ILogger $logger) {
		$this->logger = $logger;
	}

	public function for(string $appId): IRegistrationContext {
		return new class($appId, $this) implements IRegistrationContext {
			/** @var string */
			private $appId;

			/** @var RegistrationContext */
			private $context;

			public function __construct(string $appId, RegistrationContext $context) {
				$this->appId = $appId;
				$this->context = $context;
			}

			public function registerCapability(string $capability): void {
				$this->context->registerCapability(
					$this->appId,
					$capability
				);
			}

			public function registerCrashReporter(string $reporterClass): void {
				$this->context->registerCrashReporter(
					$this->appId,
					$reporterClass
				);
			}

			public function registerService(string $name, callable $factory, bool $shared = true): void {
				$this->context->registerService(
					$this->appId,
					$name,
					$factory,
					$shared
				);
			}

			public function registerServiceAlias(string $alias, string $target): void {
				$this->context->registerServiceAlias(
					$this->appId,
					$alias,
					$target
				);
			}

			public function registerParameter(string $name, $value): void {
				$this->context->registerParameter(
					$this->appId,
					$name,
					$value
				);
			}

			public function registerEventListener(string $event, string $listener, int $priority = 0): void {
				$this->context->registerEventListener(
					$this->appId,
					$event,
					$listener,
					$priority
				);
			}

			public function registerMiddleware(string $class): void {
				$this->context->registerMiddleware(
					$this->appId,
					$class
				);
			}

			public function registerSearchProvider(string $class): void {
				$this->context->registerSearchProvider(
					$this->appId,
					$class
				);
			}
		};
	}

	public function registerCapability(string $appId, string $capability): void {
		$this->capabilities[] = [
			'appId' => $appId,
			'capability' => $capability
		];
	}

	public function registerCrashReporter(string $appId, string $reporterClass): void {
		$this->crashReporters[] = [
			'appId' => $appId,
			'class' => $reporterClass,
		];
	}

	public function registerService(string $appId, string $name, callable $factory, bool $shared = true): void {
		$this->services[] = [
			"appId" => $appId,
			"name" => $name,
			"factory" => $factory,
			"sharred" => $shared,
		];
	}

	public function registerServiceAlias(string $appId, string $alias, string $target): void {
		$this->aliases[] = [
			"appId" => $appId,
			"alias" => $alias,
			"target" => $target,
		];
	}

	public function registerParameter(string $appId, string $name, $value): void {
		$this->parameters[] = [
			"appId" => $appId,
			"name" => $name,
			"value" => $value,
		];
	}

	public function registerEventListener(string $appId, string $event, string $listener, int $priority = 0): void {
		$this->eventListeners[] = [
			"appId" => $appId,
			"event" => $event,
			"listener" => $listener,
			"priority" => $priority,
		];
	}

	public function registerMiddleware(string $appId, string $class): void {
		$this->middlewares[] = [
			"appId" => $appId,
			"class" => $class,
		];
	}

	public function registerSearchProvider(string $appId, string $class) {
		$this->searchProviders[] = [
			'appId' => $appId,
			'class' => $class,
		];
	}

	/**
	 * @param App[] $apps
	 */
	public function delegateCapabilityRegistrations(array $apps): void {
		foreach ($this->capabilities as $registration) {
			try {
				$apps[$registration['appId']]
					->getContainer()
					->registerCapability($registration['capability']);
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during capability registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}
	}

	/**
	 * @param App[] $apps
	 */
	public function delegateCrashReporterRegistrations(array $apps, Registry $registry): void {
		foreach ($this->crashReporters as $registration) {
			try {
				$registry->registerLazy($registration['class']);
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during crash reporter registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}
	}

	public function delegateEventListenerRegistrations(IEventDispatcher $eventDispatcher): void {
		foreach ($this->eventListeners as $registration) {
			try {
				if (isset($registration['priority'])) {
					$eventDispatcher->addServiceListener(
						$registration['event'],
						$registration['listener'],
						$registration['priority']
					);
				} else {
					$eventDispatcher->addServiceListener(
						$registration['event'],
						$registration['listener']
					);
				}
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during event listener registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}
	}

	/**
	 * @param App[] $apps
	 */
	public function delegateContainerRegistrations(array $apps): void {
		foreach ($this->services as $registration) {
			try {
				/**
				 * Register the service and convert the callable into a \Closure if necessary
				 */
				$apps[$registration['appId']]
					->getContainer()
					->registerService(
						$registration['name'],
						Closure::fromCallable($registration['factory']),
						$registration['shared'] ?? true
					);
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during service registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}

		foreach ($this->aliases as $registration) {
			try {
				$apps[$registration['appId']]
					->getContainer()
					->registerAlias(
						$registration['alias'],
						$registration['target']
					);
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during service alias registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}

		foreach ($this->parameters as $registration) {
			try {
				$apps[$registration['appId']]
					->getContainer()
					->registerParameter(
						$registration['name'],
						$registration['value']
					);
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during service alias registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}
	}

	/**
	 * @param App[] $apps
	 */
	public function delegateMiddlewareRegistrations(array $apps): void {
		foreach ($this->middlewares as $middleware) {
			try {
				$apps[$middleware['appId']]
					->getContainer()
					->registerMiddleWare($middleware['class']);
			} catch (Throwable $e) {
				$appId = $middleware['appId'];
				$this->logger->logException($e, [
					'message' => "Error during capability registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}
	}

	/**
	 * @param App[] $apps
	 */
	public function delegateSearchProviderRegistration(array $apps, SearchComposer $searchComposer): void {
		foreach ($this->searchProviders as $registration) {
			try {
				$searchComposer->registerProvider($registration['class']);
			} catch (Throwable $e) {
				$appId = $registration['appId'];
				$this->logger->logException($e, [
					'message' => "Error during search provider registration of $appId: " . $e->getMessage(),
					'level' => ILogger::ERROR,
				]);
			}
		}
	}
}
