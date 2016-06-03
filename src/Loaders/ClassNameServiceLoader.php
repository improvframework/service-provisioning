<?php
/**
 * Copyright (c) 2016, Jim DeLois
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Jim DeLois <%%PHPDOC_AUTHOR_EMAIL%%>
 * @copyright  2016 Jim DeLois
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version    %%PHPDOC_VERSION%%
 * @link       https://github.com/improvframework/service-provisioning
 * @filesource
 *
 */

namespace Improv\ServiceProvisioning\Loaders;

use Improv\ServiceProvisioning\ServiceLoaderInterface;
use Interop\Container\ContainerInterface;

/**
 * This class takes an array of string class names for any service providers
 * that need to be invoked to register their services with the container.
 *
 * Because the process of actually registering services (from the instantiated
 * providers) into the Container may vary from implementation to implementation,
 * this class also takes a `callable` which can take an instantiated provider and
 * operate on it to ensure any services are properly set into the Container.
 *
 * This means this loader has no dependencies, and any strategy may be used
 * so long as the callable accept the instantiated provider as a first argument,
 * and the `ContainerInterface` as the second.
 *
 * ```
 *  // Build a map of service provider classes
 *  $map = [
 *      SomeServiceProvider::class,
 *      AnotherServiceProvider::class,
 *      // etc
 *  ];
 *
 *  // Instantiate the loader with the map and a callable loader
 *  $service_loader = new ClassNameServiceLoader($map, function ($provider, ContainerInterface $container) {
 *      $provider->registerServicesInto($container);
 *  } );
 *
 * // Invoke the loader.
 * // After this call, services are available to be drawn via $container->get(...)
 * $service_loader->loadServices($container);
 * ```
 *
 * Above, the `$invoker` callback is executing whatever action is necessary on the `$provider` to install its services
 * into the `$container`. If possible, any methods therein should be extracted and could at that point be type hinted
 * in the callback signature. Similarly, any class with an `__invoke` method may be passed in as the callable `$invoker`
 *
 * E.g.,
 *
 * ```
 *  class YourServiceProviderInvoker
 *  {
 *      public function __invoke(YourProviderInterface $provider, ContainerInterface $container)
 *      {
 *          $provider->registerServicesInto($container);
 *      }
 *  }
 * ```
 *
 * When using Service Providers that implement `\Improv\ServiceProvisioning\ServiceProviderInterface`,
 * consider using the existing `\Improv\ServiceProvisioning\Invokers\ServiceProviderInvoker`
 * class for this purpose.
 */
class ClassNameServiceLoader implements ServiceLoaderInterface
{

    /**
     * @var string[]
     */
    private $registry = [];

    /**
     * @var callable
     */
    private $invoker;

    /**
     * @param string[] $registry An array of strings; the class names of providers to invoke for registration
     * @param callable $invoker   The callable that will operate on each provider to register services
     */
    public function __construct(array $registry, callable $invoker)
    {
        $this->registry = $registry;
        $this->invoker  = $invoker;
    }

    /**
     * {@inheritdoc}
     */
    public function loadServices(ContainerInterface $container)
    {
        $classes = array_unique($this->registry);
        $invoker = $this->invoker;

        array_walk($classes, function ($class) use ($invoker, $container) {

            if (!\class_exists($class)) {
                throw new \RuntimeException(sprintf('Class "%s" was not found to be real or loadable.', $class));
            }

            $invoker(new $class, $container);

        });
    }
}
