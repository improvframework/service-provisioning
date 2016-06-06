[![Build Status](https://travis-ci.org/improvframework/service-provisioning.svg?branch=master)](https://travis-ci.org/improvframework/service-provisioning)
[![Dependency Status](https://www.versioneye.com/user/projects/575366517757a00041b3a3e0/badge.svg?style=flat)](https://www.versioneye.com/user/projects/575366517757a00041b3a3e0)
[![Code Climate](https://codeclimate.com/github/improvframework/service-provisioning/badges/gpa.svg)](https://codeclimate.com/github/improvframework/service-provisioning)
[![Coverage Status](https://coveralls.io/repos/improvframework/service-provisioning/badge.svg?branch=master&service=github)](https://coveralls.io/github/improvframework/service-provisioning?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/improvframework/service-provisioning.svg)](http://hhvm.h4cc.de/package/improvframework/service-provisioning)

# Improv Framework - Service Provisioning

A ContainerInterop-compatible package intended to ease the organization and loading of container services.

## Motivation ##

A Dependency Injection Container is primarily used to retrieve fully-configured services that are shared througout an application. In order to fetch these services, they must be installed or registered into the Container. This package aims to simplify the registration process.

### The Problem ###

The documentation for many frameworks and containers will illustrate some sort of trivial "Hello World"-esque example.  These steups often include something like a `container.php`, a `config.php`, or a `bootstrap.php`, etc, within which all of the example application's services are installed into the shared container.  When this file must grow to support slightly more functionality, things can quickly start to get busy.  E.g.,

```php
<?php // File: container.php

$container = new \Some\Container();

$container['routing.table'] = function (Container $container) {
	return new RoutingTable($container->get('config'));
}

$container['routing.router'] = function (Container $container) {
	return new Router($container->get('routing.table'));
};

$container['config'] = function () {
	return new Configuration(new ConfigLoader('/config.yml'));
};

$container['application'] = function (Container $container) {
	return new Application($container->get('routing.router'));
};

$container['db'] = function (Container $container) {
	return new DatabaseFactory::create(Database::TYPE_PDO, $container->get('config'));
};

$container['repository.blog'] = function (Container $container) {
	return new BlogRepository($container->get('db'));
};

$container['repository.user'] = function (Container $container) {
	return new UserRepository($container->get('db'));
};

$container['service.blog'] = function (Container $container) {
	return new BlogService($container->get('repository.blog'))
};

$container['service.user'] = function (Container $container) {
	return new UserService($container->get('repository.user'))
};

$container['controller.blog'] = function (Container $container) {
	return new BlogController($container->get('service.blog'));
};

$container['controller.user'] = function (Container $container) {
	return new UserController($container->get('service.user'));
};

// etc.

return $container;
```
This helps to keep application entrypoints nice and trim, like HTTP front-controllers, CLI scripts, crons, etc.

```php
<?php // File public/index.php

$container   = require_once('../container.php');
$application = $container->get('application');

$application->run();
```

The example above is trite and a bit naive, but the problem is evident. We have only a few main services configured, with just a couple of domain entities (Blog and User, here).  Imagine a much larger application, with several more entities to manage. It also has each service being instantiated with no further operations, whereas many objects often need tweaking or configuration before being returned. Moreover, this container has nothing by way of factories, validators, event dispatchers, loggers, profilers, formatters, authentication services, read/write database, cache storage, etc.  It's not difficult to see that "inline" container management becomes unwieldy almost immediately, and practically impossible in all but the smallest of real-world applications.

There are two things occurring in the above example that are required to initialize the container for the script run.  First, the services are being **defined and configured** in `container.php`.  That, in and of itself, does nothing for the running PHP process unless this file is invoked by being included within `index.php`, causing the **loading** of the services at that time. <sup>&dagger;</sup>

<sup>&dagger;</sup> *Technically, the "loading" of services into memory is done lazily, but conceptually it occurs here, because the inclusion of the file is necessary for any loading to ever take place. These details are not relevant to the problem space.*

### The Solution ###

The Improv Service Provisioning library sets out to resolve the above issues.

#### ServiceLoaders ####

The awkward line of `$configuration = include('../container.php');` can be abstracted away by introducing a layer whose only responsibility is to "load" any and all services for the application. Conceptually, this may be as simple as replacing the `include` line with a call to a loader class's method such as `loadServices`.

```php
<?php // File public/index.php

$container = new Container();
(new Custom\App\ServiceLoader())->loadServices($container);

$container->get('application')->run();
```
We can think of this class or layer as a **ServiceLoader** in that its job is to "load" the services into the running application.

The benefit to this approach is that the action of loading of services becomes testable, and encapsulates the details about _how_ the loading is happening.  Whether it's actually just reading in the same huge `container.php` file, or leveraging several other files under the hood, it's hidden away and the loading becomes more reusable as a result. Instead of reading in files, it may even call on another layer of classes to define services (seen next).  Further, the swapping or mixing of loading strategies becomes possible, without affecting the consuming application.

#### ServiceProviders ####

Whether or not we use a ServiceLoader, as described above, using a single file or set of files can still leave us with a complicated mess.

We can avoid this scenario by forming logical groups of associated services into classes of their own. Such a class might be known as a **ServiceProvider**, in the sense that this separate class _provides_ suites of related _services_ to the application.

The positive consequences of this are similar to those above. Providers encapsulate their speicifc implementation logic and become testable units, as well.  They become more legible, compact, and easier to reason about. Providers can even be extracted to packages alongside their services and re-used across applications.

## Package Installation ##

### Using Composer (Recommended) ###

```
composer require improvframework/service-provisioning
```

### Manual ###

Each release is available for download from the [releases page on Github](https://github.com/improvframework/service-provisioning/releases). Alternatively, you may fork, clone, and [build the package](#buildpackage). Then, install into the location of your choice.

This package conforms to PSR-4 autoloading standards.

## Usage ##

### ServiceLoaderInterace ###

The interface `\Improv\ServiceProvisioning\ServiceLoaderInterface` can be used to encapsulate any strategy desired for attaching services to an `\Interop\Container\ContainerInterface` container. This can be done by implementing the `loadServices(ContainerInterface $continer)` method of the interface.

As an example, we can look at one such strategy included within this package.

#### ClassNameServiceLoader ####

The class `\Improv\ServiceProvisioning\Loaders\ClassNameServiceLoader` is a concrete implentation of the `ServiceLoaderInterface`. It takes an array of string class names, instantiates every one, and operates on each using a passed-in callback to attach it to the Container.

```php
// Build a map of classes to search for and instantiate
$map = [
    SomeServiceProvider::class,
    AnotherServiceProvider::class,
    // etc
];

// Create the loader, providing the map and a callable which
// will operate on each of the above classes in some way.
$service_loader = new ClassNameServiceLoader($map, function ($subject, ContainerInterface $container) {
    $subject->registerServicesInto($container);
} );

// Invoke the loading action. This will iterate the classes
// from $map and apply the callback to each. After this call,
// services are available to be drawn via $container->get(...)
$service_loader->loadServices($container);
```

The `$subject` may be operated on, have a method called upon it, or whatever is necessary.

Assuming all classes in the `$map` are of the same type, they could potentially be type hinted in the callback signature. Similarly, any class with an `__invoke` method may be passed in as the callable, e.g.:

```php
class CustomServiceInvoker
{
    public function __invoke(CustomServiceInterface $subject, ContainerInterface $container)
    {
        $subject->registerServicesInto($container);
    }
}
```

Using a callback to "invoke" the attachment of the service into the Container means that this implementation of the `ServiceLoaderInterface` can be used with any other Container library (e.g. `Pimple` or `\League\Container`, something custom, etc), bridging a gap between proprietary code and `ContainerInterop`.

This library also offers its own brand of service providers, covered next.

### ServiceProviderInterface ###

The `\Improv\ServiceProvisioning\ServiceProviderInterface` defines a `register(ContainerInterface $container)` signature.

As pointed out in the sections above, it is often useful to aggregate services (classes that need to be registered into a Container) into logical groupings of related functionality.  E.g., adding "User" functionality may require the setting of several services on the container; a controller, some services, a repository, etc. These items could be grouped together in a "ServiceProvider" such that the provider may encapsulate the registration details of all the services related to the "module".

Calling `register` on the ServiceProvider should install into the Container all services of the module. In this
sense, the implementation _Provides_ to the application a suite of _Services_.

An implementation from our "problem" example, above, may look like:

```php
class UserModuleServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container)
    {
		$container['repository.user'] = function (Container $container) {
			return new UserRepository($container->get('db'));
		};

		$container['service.user'] = function (Container $container) {
			return new UserService($container->get('repository.user'))
		};

		$container['controller.user'] = function (Container $container) {
			return new UserController($container->get('service.user'));
		};

    }
}

// After this call, the User-related services are now
// available to be retrieved from the container.
(new UserModuleServiceProvider())->register($container);
```


### Tying it Together ###

It should be noted that the `ServiceProviderInterface` and the `ServiceLoaderInterface` (along with its concretions) bundled in this package have no dependencies on one another.  The use of one does not require (nor does it preclude) the use of the other.

However, these concepts go hand-in-hand. For projects starting anew or migrating to one or the other, it may make sense to leverage both. For this reason, and because the footprint of each is small, both are provided within this same package. This may change in the future.

#### ServiceProviderInvoker ####

Should both interfaces be put to use in the same project and, more, an extension that uses the "callable" approach be used as a Locator, there is one more convenience class provided in this package to bridge the gap between the two.

As stated earlier, the `callable $invoker` injected into, say, the `ClassNameServiceLoader` may come in the form of class instead of a lambda.  Because the act of installing an Improv `ServiceProviderInterface` implementation into the Container simply requires calling `register` on the provider, it is trivial to create a class that does this for us when invoked as a callable.  The `\Improv\ServiceProvisioning\Invokers\ServiceProviderInvoker` class does exactly that.

As such, an updated example may look like:

```php
// Build a map of service providers, each of which implement this
// package's \Improv\ServiceProvisioning\ServiceProviderInterface
$map = [
	CoreServiceProvider::class,
	PersistenceServiceProvider::class,
    UserModuleServiceProvider::class,
    BlogModuleServiceProvider::class,
    // etc
];

// Instantiate the loader with the map and this package's invoker
$service_loader = new ClassNameServiceLoader($map, new ServiceProviderInvoker() );

$service_loader->loadServices($container);
```

Because the classes in the `$map` each implement the `ServiceProviderInterface` from this package, the `ServiceProviderInvoker` included knows how to attach them to the Container.

### Conclusion ###

At this point, the `container.php` file is completely eliminated in favor of smaller, separated providers capable of being individually read in by the autoloader. There is a testable `ServiceLoader` which can be configured with the right strategy for loading in services and providers, itself being passed or instantiated wherever it makes the most sense for the consuming application.


## To Do ##

  - Provide more "Loader" implementations
  - Provide Contribution Notes
  - Consider adding Eventing to loading and registration implementations 


## Notes and Issues ##
Please note that this is a new package, currently in beta. Feel free to reach out with ideas, bug reports, or contribution questions.

## Additional Documentation

You may [run the API Doc build target](#buildtargets) to produce and peruse API documentation for this package.

## <a name="buildtest"></a>Running the Build/Test Suite

This package makes extensive use of the [Phing](https://www.phing.info/ "Click to Learn More") build tool.

Below is a list of notable build targets, but please feel free to peruse the `build.xml` file for more insight.

### Default Target

`./vendor/bin/phing` will execute the `build` target (the same as executing `./vendor/bin/phing build`).
This performs a linting, syntax check, runs all static analysis tools, the test suite, and produces API documentation.

### <a name="buildpackage"></a>"Full" Packaging Target

Executing `./vendor/bin/phing package` will run all above checks and, if passing, package the source into a shippable file
with only the relevant source included therein.

### <a name="buildtargets"></a>Selected Individual Targets
 
- Run the Tests
    - `./vendor/bin/phing test`
    - `./vendor/bin/phpunit`
- Perform Static Analysis
    - `./vendor/bin/phing static-analysis`
    - The generated reports are in `./build/output/reports`
- Produce API Documentation
    - `./vendor/bin/phing documentapi`
    - The generated documentation is in `./build/docs/api`
- Build Package from Source
    - `./vendor/bin/phing package`
    - The artifacts are in `./build/output/artifacts`



[![License](https://poser.pugx.org/improvframework/service-provisioning/license)](https://packagist.org/packages/improvframework/service-provisioning)
[![Latest Stable Version](https://poser.pugx.org/improvframework/service-provisioning/v/stable)](https://packagist.org/packages/improvframework/service-provisioning)
[![Latest Unstable Version](https://poser.pugx.org/improvframework/service-provisioning/v/unstable)](https://packagist.org/packages/improvframework/service-provisioning)
[![Total Downloads](https://poser.pugx.org/improvframework/service-provisioning/downloads)](https://packagist.org/packages/improvframework/service-provisioning)
