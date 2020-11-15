<?php

declare(strict_types=1);

namespace Rector\Core\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AutowiredEventDispatcher extends EventDispatcher
{
    /**
     * @param EventSubscriberInterface[] $eventSubscribers
     */
    public function __construct(array $eventSubscribers)
    {
        foreach ($eventSubscribers as $eventSubscriber) {
            $this->addSubscriber($eventSubscriber);
        }

        // Symfony 4.4/5 compat
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
    }
}
