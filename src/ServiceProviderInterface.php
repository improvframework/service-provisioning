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

namespace Improv\ServiceProvisioning;

use Interop\Container\ContainerInterface;

/**
 * Interface to ease the organization of logical groupings of services during application startup.
 * Intended to "offer up" suites of services to an application.
 *
 * It is often useful to aggregate services (classes that need to be registered into a Container)
 * into logical groupings of related functionality.  E.g., adding "User" functionality may require
 * the setting of several services on the container; a controller, some services, a repository, etc.
 * These items could be grouped together in a "ServiceProvider" such that then provider may encapsulate
 * the registration details of all the services related to its "module".
 *
 * Calling `register` with the Container should install into it all services of the module. In this
 * sense, the implementation "Provides" to the application a suite of Services.
 *
 * ```
 *  class SomeComponentServiceProvider implements ServiceProviderInterface
 *  {
 *      public function register(ContainerInterface $container)
 *      {
 *          $container['service_component'] = function () {
 *              return new SomeServiceComponent();
 *          }
 *
 *          $container['another_component] = function (ContainerInterface $container) {
 *              return new AnotherComponent($container->get('service_component');
 *          }
 *
 *          // etc
 *      }
 *  }
 *
 *  // After this call, the multiple services are now available to be retrieved from the container.
 *  (new SomeComponentServiceProvider())->register($container);
 * ```
 *
 * @package Improv\ServiceProvisioning
 */
interface ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     *
     * @return void
     *
     * @throw \RuntimeException  If the service cannot be registered
     */
    public function register(ContainerInterface $container);
}
