<?php

namespace Improv\ServiceProvisioning\Invokers;

use Improv\ServiceProvisioning\ServiceProviderInterface;
use Improv\ServiceProvisioning\Test\AbstractTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Improv\ServiceProvisioning\Invokers\ServiceProviderInvoker
 */
class ServiceProviderInvokerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function instanceHasRegisterCalledWithContainerInput()
    {
        $sut       = new ServiceProviderInvoker;

        $provider  = $this->createMock(ServiceProviderInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $provider->expects(static::once())
            ->method('register')
            ->with($container);

        $sut($provider, $container);
    }
}
