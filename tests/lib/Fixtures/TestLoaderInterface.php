<?php

namespace Improv\ServiceProvisioning\Test\Fixtures;

use Interop\Container\ContainerInterface;

interface TestLoaderInterface
{
    public function __invoke($provider, ContainerInterface $container);
}
