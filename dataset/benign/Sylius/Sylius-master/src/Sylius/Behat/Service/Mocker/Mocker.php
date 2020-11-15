<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Behat\Service\Mocker;

use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;

class Mocker implements MockerInterface
{
    /** @var MockerContainer */
    private $container;

    public function __construct(MockerContainer $container)
    {
        $this->container = $container;
    }

    public function mockCollaborator($className)
    {
        return \Mockery::mock($className);
    }

    public function mockService($serviceId, $className)
    {
        return $this->container->mock($serviceId, $className);
    }

    public function unmockService($serviceId)
    {
        $this->container->unmock($serviceId);
    }

    public function unmockAll()
    {
        $mockedServices = $this->container->getMockedServices();

        foreach ($mockedServices as $mockedServiceId => $mockedService) {
            $this->container->unmock($mockedServiceId);
        }
    }
}
