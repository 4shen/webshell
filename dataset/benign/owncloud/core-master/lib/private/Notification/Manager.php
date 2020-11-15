<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\Notification;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OCP\Notification\IApp;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\Exceptions\NotifierIdInUseException;
use OCP\Notification\Events\RegisterConsumerEvent;
use OCP\Notification\Events\RegisterNotifierEvent;
use OC\Notification\Events\RegisterConsumerEventImpl;
use OC\Notification\Events\RegisterNotifierEventImpl;

class Manager implements IManager {
	/** @var EventDispatcherInterface */
	protected $dispatcher;

	/** @var IApp[] */
	protected $apps;

	/** @var INotifier */
	protected $notifiers;

	/** @var array[] */
	protected $notifiersInfo;

	/** @var \Closure[] */
	protected $appsClosures;

	/** @var \Closure[] */
	protected $notifiersClosures;

	/** @var \Closure[] */
	protected $notifiersInfoClosures;

	/** @var IApp[] */
	protected $builtAppsHolder;

	/** @var INotifier[] */
	protected $builtNotifiersHolder;

	public function __construct(EventDispatcherInterface $dispatcher) {
		$this->dispatcher = $dispatcher;

		$this->apps = [];
		$this->notifiers = [];
		$this->notifiersInfo = [];
		$this->appsClosures = [];
		$this->notifiersClosures = [];
		$this->notifiersInfoClosures = [];

		$this->builtAppsHolder = [];
	}

	/**
	 * @param \Closure $service The service must implement IApp, otherwise a
	 *                          \InvalidArgumentException is thrown later
	 * @return void
	 * @since 8.2.0
	 */
	public function registerApp(\Closure $service) {
		$this->appsClosures[] = $service;
		$this->apps = [];
	}

	/**
	 * @param \Closure $service The service must implement INotifier, otherwise a
	 *                          \InvalidArgumentException is thrown later
	 * @param \Closure $info    An array with the keys 'id' and 'name' containing
	 *                          the app id and the app name
	 * @return void
	 * @since 8.2.0 - Parameter $info was added in 9.0.0
	 */
	public function registerNotifier(\Closure $service, \Closure $info) {
		$this->notifiersClosures[] = $service;
		$this->notifiersInfoClosures[] = $info;
		$this->notifiers = [];
		$this->notifiersInfo = [];
	}

	/**
	 * INTERNAL USE ONLY!! This method isn't part of the IManager interface
	 * @internal This should only be used by the RegisterConsumerEventImpl (the real implementation).
	 * Do NOT use this method outside as it might not work as expected.
	 */
	public function registerBuiltApp(IApp $app) {
		$this->builtAppsHolder[] = $app;
	}

	/**
	 * INTERNAL USE ONLY!! This method isn't part of the IManager interface
	 * @internal This should only be used by the RegisterNotifierEventImpl (the real implementation).
	 * Do NOT use this method outside as it might not work as expected.
	 */
	public function registerBuiltNotifier(INotifier $notifier, $id, $name) {
		if (!isset($this->builtNotifiersHolder[$id]) && !isset($this->notifiersInfo[$id])) {
			// we have to check also in the notifiersInfo
			$this->builtNotifiersHolder[$id] = [];
			$this->builtNotifiersHolder[$id]['notifier'] = $notifier;
			$this->builtNotifiersHolder[$id]['name'] = $name;
		} else {
			throw new NotifierIdInUseException("The given notifier ID $id is already in use");
		}
	}

	/**
	 * @return IApp[]
	 */
	protected function getApps() {
		if (!empty($this->apps)) {
			return $this->apps;
		}

		$this->apps = [];
		foreach ($this->appsClosures as $closure) {
			$app = $closure();
			if (!($app instanceof IApp)) {
				throw new \InvalidArgumentException('The given notification app does not implement the IApp interface');
			}
			$this->apps[] = $app;
		}

		$this->builtAppsHolder = [];
		$registerAppEvent = new RegisterConsumerEventImpl($this);
		$this->dispatcher->dispatch($registerAppEvent, RegisterConsumerEvent::NAME);
		$this->apps = \array_merge($this->apps, $this->builtAppsHolder);

		return $this->apps;
	}

	/**
	 * @return INotifier[]
	 */
	protected function getNotifiers() {
		if (!empty($this->notifiers)) {
			return $this->notifiers;
		}

		$this->notifiers = [];
		foreach ($this->notifiersClosures as $closure) {
			$notifier = $closure();
			if (!($notifier instanceof INotifier)) {
				throw new \InvalidArgumentException('The given notifier does not implement the INotifier interface');
			}
			$this->notifiers[] = $notifier;
		}

		$this->builtNotifiersHolder = [];
		$registerNotifierEvent = new RegisterNotifierEventImpl($this);
		$this->dispatcher->dispatch($registerNotifierEvent, RegisterNotifierEvent::NAME);
		foreach ($this->builtNotifiersHolder as $notifierData) {
			$this->notifiers[] = $notifierData['notifier'];
		}

		return $this->notifiers;
	}

	/**
	 * @return array[]
	 */
	public function listNotifiers() {
		if (!empty($this->notifiersInfo)) {
			return $this->notifiersInfo;
		}

		$this->notifiersInfo = [];
		foreach ($this->notifiersInfoClosures as $closure) {
			$notifier = $closure();
			if (!\is_array($notifier) || \sizeof($notifier) !== 2 || !isset($notifier['id']) || !isset($notifier['name'])) {
				throw new \InvalidArgumentException('The given notifier information is invalid');
			}
			if (isset($this->notifiersInfo[$notifier['id']])) {
				throw new \InvalidArgumentException('The given notifier ID ' . $notifier['id'] . ' is already in use');
			}
			$this->notifiersInfo[$notifier['id']] = $notifier['name'];
		}

		$this->builtNotifiersHolder = [];
		$registerNotifierEvent = new RegisterNotifierEventImpl($this);
		$this->dispatcher->dispatch($registerNotifierEvent, RegisterNotifierEvent::NAME);
		foreach ($this->builtNotifiersHolder as $id => $notifierData) {
			$this->notifiersInfo[$id] = $notifierData['name'];
		}

		return $this->notifiersInfo;
	}

	/**
	 * @return INotification
	 * @since 8.2.0
	 */
	public function createNotification() {
		return new Notification();
	}

	/**
	 * @return bool
	 * @since 8.2.0
	 */
	public function hasNotifiers() {
		return !empty($this->notifiersClosures);
	}

	/**
	 * @param INotification $notification
	 * @return void
	 * @throws \InvalidArgumentException When the notification is not valid
	 * @since 8.2.0
	 */
	public function notify(INotification $notification) {
		if (!$notification->isValid()) {
			throw new \InvalidArgumentException('The given notification is invalid');
		}

		$apps = $this->getApps();

		foreach ($apps as $app) {
			try {
				$app->notify($notification);
			} catch (\InvalidArgumentException $e) {
			}
		}
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 * @since 8.2.0
	 */
	public function prepare(INotification $notification, $languageCode) {
		$notifiers = $this->getNotifiers();

		foreach ($notifiers as $notifier) {
			try {
				$notification = $notifier->prepare($notification, $languageCode);
			} catch (\InvalidArgumentException $e) {
				continue;
			}

			if (!($notification instanceof INotification) || !$notification->isValidParsed()) {
				throw new \InvalidArgumentException('The given notification has not been handled');
			}
		}

		if (!($notification instanceof INotification) || !$notification->isValidParsed()) {
			throw new \InvalidArgumentException('The given notification has not been handled');
		}

		return $notification;
	}

	/**
	 * @param INotification $notification
	 * @return void
	 */
	public function markProcessed(INotification $notification) {
		$apps = $this->getApps();

		foreach ($apps as $app) {
			$app->markProcessed($notification);
		}
	}

	/**
	 * @param INotification $notification
	 * @return int
	 */
	public function getCount(INotification $notification) {
		$apps = $this->getApps();

		$count = 0;
		foreach ($apps as $app) {
			$count += $app->getCount($notification);
		}

		return $count;
	}
}
