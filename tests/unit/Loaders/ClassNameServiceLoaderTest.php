<?php

namespace Improv\ServiceProvisioning\Loaders;

use Improv\ServiceProvisioning\ServiceProviderInterface;
use Improv\ServiceProvisioning\Test\AbstractTestCase;
use Improv\ServiceProvisioning\Test\Fixtures\TestLoaderInterface;
use PSR\Container\ContainerInterface;

/**
 * @covers \Improv\ServiceProvisioning\Loaders\ClassNameServiceLoader
 */
class ClassNameServiceLoaderTest extends AbstractTestCase
{
    /**
     * @test
     * @dataProvider loadServicesDataProvider
     */
    public function loadServices(array $providers, $count)
    {

        $container = $this->createMock(ContainerInterface::class);
        $loader    = $this->createMock(TestLoaderInterface::class);

        $expected  = array_map(function ($provider) use ($container) {
            return [ $provider, $container ];
        }, $providers);

        $loader->expects($count)
            ->method('__invoke')
            ->withConsecutive(
                ...$expected
            );

        $map = array_map(function ($provider) {
            return get_class($provider);
        }, $providers);

        $sut = new ClassNameServiceLoader($map, $loader);

        $sut->loadServices($container);
    }

    /**
     * @test
     */
    public function raiseExceptionOnInvalidProviderClass()
    {
        $container = $this->createMock(ContainerInterface::class);
        $loader    = $this->createMock(TestLoaderInterface::class);
        $map       = [ 'NoSuchClassExists' ];

        $sut       = new ClassNameServiceLoader($map, $loader);

        $this->expectException(\RuntimeException::class);
        $sut->loadServices($container);
    }

    /**
     * @return array
     */
    public function loadServicesDataProvider()
    {

        // Note that we need to explicitly set the class names to be different
        //  since the array is made unique in ClassNameServiceLoader::loadServices

        $provider_one   = $this->getMockBuilder(ServiceProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('Mock_Provider_1')
            ->getMock();

        $provider_two   = $this->getMockBuilder(ServiceProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('Mock_Provider_2')
            ->getMock();

        $provider_three = $this->getMockBuilder(ServiceProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('Mock_Provider_3')
            ->getMock();

        return [
            [ [ $provider_one ],                                                static::once() ],
            [ [ $provider_one, $provider_two, $provider_three ],                static::exactly(3) ],
            // Uniqueness Test
            [ [ $provider_one, $provider_two, $provider_three, $provider_one ], static::exactly(3) ],
        ];
    }
}
