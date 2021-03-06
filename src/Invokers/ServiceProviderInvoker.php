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
 * @copyright  2018 Jim DeLois
 * @license    http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version    %%PHPDOC_VERSION%%
 * @link       https://github.com/improvframework/service-provisioning
 * @filesource
 *
 */

namespace Improv\ServiceProvisioning\Invokers;

use Improv\ServiceProvisioning\ServiceProviderInterface;
use PSR\Container\ContainerInterface;

/**
 * A convenience class that can be used as a callable in order to "register" into the Container any
 * application-specific Services that implement `\Improv\ServiceProvisioning\ServiceProviderInterface`.
 *
 * This becomes particularly useful when leveraging an Improv Service Loader such as
 * `\Improv\ServiceProvisioning\Loaders\ClassNameServiceLoader` (e.g.), which takes an instance of a Service Provider
 * and a callable "invoker" that then operates on the given Provider. When using instances of Improv Providers, this
 * class can do the wiring.
 *
 * ```
 *  // Build a map of service providers that implement \Improv\ServiceProvisioning\ServiceProviderInterface
 *  $map = [
 *      SomeServiceProvider::class,
 *      AnotherServiceProvider::class,
 *      // etc
 *  ];
 *
 *  // Instantiate the loader with the map and this invoker
 *  $service_loader = new ClassNameServiceLoader($map, new ServiceProviderInvoker());
 *
 *  // Wire all the service
 *  $service_loader->loadServices($container);
 * ```
 *
 * @package Improv\ServiceProvisioning\Invokers
 */
class ServiceProviderInvoker
{
    /**
     * @param ServiceProviderInterface $provider
     * @param ContainerInterface       $container
     */
    public function __invoke(ServiceProviderInterface $provider, ContainerInterface $container)
    {
        $provider->register($container);
    }
}
