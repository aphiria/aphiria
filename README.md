<h1>Configuration</h1>

[![Build Status](https://travis-ci.com/aphiria/configuration.svg)](https://travis-ci.com/aphiria/configuration)
[![Latest Stable Version](https://poser.pugx.org/aphiria/configuration/v/stable.svg)](https://packagist.org/packages/aphiria/configuration)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/configuration/v/unstable.svg)](https://packagist.org/packages/aphiria/configuration)
[![License](https://poser.pugx.org/aphiria/configuration/license.svg)](https://packagist.org/packages/aphiria/configuration)

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Installation](#installation)
2. [Application Builder](#application-builder)
    1. [Configuring Bootstrappers](#configuring-bootstrappers)
    2. [Configuring Routes](#configuring-routes)
    3. [Configuring Console Commands](#configuring-console-commands)
3. [Modules](#modules)

<h1 id="introduction">Introduction</h1>

Aphiria comes with an easy way to centrally configure your application logic, eg bootstrappers, routes, and console commands.  It even lets you centralize the configuration of entire [modules](#modules) of code.

<h2 id="installation">Installation</h2>

This library requires PHP 7.3 and above.  It can be installed via <a href="https://getcomposer.org/" target="_blank">Composer</a> by including the following in your _composer.json_:

```bash
"aphiria/configuration": "1.0.*@dev"
```

<h1 id="application-builder">Application Builder</h1>

`ApplicationBuilder` provides all the functionality you'll need to configure your application logic:

```php
use Aphiria\Configuration\ApplicationBuilder;
use Opulence\Ioc\Bootstrappers\Inspections\BindingInspectorBootstrapperDispatcher;
use Opulence\Ioc\Container;

$container = new Container();
$bootstrapperDispatcher = new BindingInspectorBootstrapperDispatcher($container);
$appBuilder = new ApplicationBuilder($container, $bootstrapperDispatcher);
```

Once you're done configuring your [bootstrappers](#configuring-bootstrappers), [routes](#configuring-routes), and [console commands](#configuring-console-commands), you can go ahead and build your application:

```php
$appBuilder->build();
```

Your application logic is now all set to go.

<h2 id="configuring-bootstrappers">Configuring Bootstrappers</h2>

Let's take a look at how to configure bootstrappers:

```php
$appBuilder->withBootstrappers(function () {
    return [FooBootstrapper::class];
});
```

<h2 id="configuring-routes">Configuring Routes</h2>

We can register some routes to your application:

```php
$appBuilder->withRoutes(function (RouteBuilderRegistry $routes) {
    $routes->map('GET', 'users/:id')
        ->toMethod(UserController::class, 'getUserById');
});
```

Due to how lazy route creation works, your routes will only be built if they need to be, eg they're not cached yet.

<h2 id="configuring-console-commands">Configuring Console Commands</h2>

Now, we'll add some console commands:

```php
$appBuilder->withCommands(function (CommandRegistry $commands) {
    // You can also use $commands->registerManyCommands()
    $commands->registerCommand(
        new FooCommand(),
        function () {
            return new FooCommandHandler();
        }
    );
});
```

<h1 id="modules">Modules</h1>

A module is a chunk of your domain.  For example, if you are running a site where users can buy books, you might have a user module, a book module, and a shopping cart module.  Each of these modules will have separate bootstrappers, routes, and console commands.  So, why not bundle all the configuration logic by module?

```php
use Aphiria\Configuration\IModuleBuilder;
use Aphiria\Routing\Builders\RouteGroupOptions;

final class UserModuleBuilder implements IModuleBuilder
{
    public function build(IApplicationBuilder $appBuilder): void
    {
        $appBuilder->withBootstrappers(function () {
            return [UserServiceBootstrapper::class];
        });
        
        $appBuilder->withRoutes(function (RouteBuilderRegistry $routes) {
            // Let's prefix all our routes with 'users'
            $routes->group(new RouteGroupOptions('users'), function (RouteBuilderRegistry $routes) {
                $routes->map('GET', '/:id')
                    ->toMethod(UserController::class, 'getUserById');
            });
        });
        
        $appBuilder->withCommands(function (CommandRegistry $commands) {
            $commands->registerCommand(
                new RunUserReportCommand(),
                function () {
                    return new RunUserReportCommandHandler();
                }
            );
        });
    }
}
```

To use your module in your application builder, just call:

```php
$appBuilder->withModule(new UserModuleBuilder());
$appBuilder->build();
```

Now, your entire user module is configured and ready to go.