<?php

namespace Improv\ServiceProvisioning\Test\Fixtures;

use PSR\Container\ContainerInterface;

interface TestLoaderInterface
{
    public function __invoke($provider, ContainerInterface $container);
}
