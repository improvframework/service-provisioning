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
 * Bootstraps the loading of services needed by a container. Can be though of as a
 * "reader" and "registrar" of services.
 *
 * A `ServiceLoader` is any class that orchestrates and instantiates multiple service
 * providers (which themselves aggregate services into groups), invoking whatever is
 * necessary on those providers to ultimately attach services to the given Container.
 *
 * The result of calling `loadServices` is that the Container is populated with services
 * readied to be drawn from it. This should happen early in the application lifecycle, and
 * is likely to occur during bootstrap, within some Application object's "run" or other
 * invoking method.
 *
 * There is no one correct way to define how service providers should be located,
 * registered, or instantiated, etc, so an implementation of the `ServiceLoaderInterface`
 * allows the strategy to vary from application to application.
 */
interface ServiceLoaderInterface
{
    /**
     * Executes the implementation algorithm for loading services into the Container
     *
     * @param ContainerInterface $container
     *
     * @return void
     *
     * @throws \RuntimeException If unable to load a service provider class
     */
    public function loadServices(ContainerInterface $container);
}
