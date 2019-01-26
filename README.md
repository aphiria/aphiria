<h1>Configuration</h1>

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
2. [Built-In Settings](#built-in-settings)
3. [Custom Settings](#custom-settings)

<h1 id="introduction">Introduction</h1>

This library helps simplify how you configure an <a href="https://github.com/opulencephp/app" target="_blank">application that uses Opulence</a>.  It's also a convenient place to put any custom configuration settings.

> **Note:** This library is best used when configuring the application layer of your app.  Using it in your domain layer is a code smell, and should probably be avoided.

<h1 id="built-in-settings">Built-In Settings</h1>

`Config` provides some built-in settings that will be common to any application that uses Opulence.  They must be passed in via the constructor:

```php
use Opulence\Configuration\Config;

$config = new Config(
    $paths, // Slugs to file paths of important files
    $exceptionHandler, // The app exception handler
    $logger, // The logger
    $container, // The DI container
    $routeMatcher, // The route matcher
    $contentNegotiator // The content negotiator
);
```

These built-in settings are accessible as properties of `Config`:

```php
$config->paths;
$config->exceptionHandler;
$config->logger;
$config->container;
$config->routeMatcher;
$config->contentNegotiator;
```

<h1 id="custom-settings">Custom Settings</h1>

Most applications will need to set custom settings that are specific to their applications.  `Config` provides a few methods to make this easy:

```php
$config->set('cookies', 'ttl', 3600);
```

In this example, `cookies` is the category of the setting.  Categorizing your settings reduces naming conflicts.  You may also set an entire category at once:

```php
$config->setCategory('cookies', ['ttl' => 3600]);
```

To grab the setting, call:

```php
$cookieTtl = $config->get('cookies', 'ttl');
```

You may also specify a default value in case one isn't set:

```php
$cookieTtl = $config->get('cookies', 'ttl', 3600);
```

If you need to check if a setting exists, call:

```php
if ($config->has('cookies', 'ttl')) {
    // ...
}
```