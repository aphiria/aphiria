<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Application;

use Aphiria\Application\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use Aphiria\Authentication\AuthenticationScheme;
use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Authentication\AuthenticationSchemeRegistry;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\IAuthorizationRequirementHandler;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Authentication\Components\AuthenticationComponent;
use Aphiria\Framework\Authorization\Components\AuthorizationComponent;
use Aphiria\Framework\Console\Commands\FlushFrameworkCachesCommand;
use Aphiria\Framework\Console\Commands\FlushFrameworkCachesCommandHandler;
use Aphiria\Framework\Console\Commands\ServeCommand;
use Aphiria\Framework\Console\Commands\ServeCommandHandler;
use Aphiria\Framework\Console\Components\CommandComponent;
use Aphiria\Framework\DependencyInjection\Components\BinderComponent;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Framework\Routing\Commands\RouteListCommand;
use Aphiria\Framework\Routing\Commands\RouteListCommandHandler;
use Aphiria\Framework\Routing\Components\RouterComponent;
use Aphiria\Framework\Validation\Components\ValidationComponent;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Routing\RouteCollectionBuilder;
use Aphiria\Validation\ObjectConstraintsRegistryBuilder;
use Closure;
use Exception;
use RuntimeException;

/**
 * Defines the trait that simplifies interacting with Aphiria components
 */
trait AphiriaComponents
{
    /**
     * Adds a component to the application builder
     * Note: This is to simply a syntactic sugar method to make it easier to chain things
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param IComponent $component The component to add
     * @return static For chaining
     */
    public function withComponent(IApplicationBuilder $appBuilder, IComponent $component): static
    {
        $appBuilder->withComponent($component);

        return $this;
    }

    /**
     * Adds an authentication scheme to the authenticator
     *
     * @template T of AuthenticationSchemeOptions
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param AuthenticationScheme<T> $scheme The scheme to register
     * @param bool $isDefault Whether or not the scheme is the default scheme
     * @return static For chaining
     */
    protected function withAuthenticationScheme(
        IApplicationBuilder $appBuilder,
        AuthenticationScheme $scheme,
        bool $isDefault = false
    ): static {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(AuthenticationComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the authentication scheme registry here so that it can be used in the component
            if (!Container::$globalInstance->hasBinding(AuthenticationSchemeRegistry::class)) {
                Container::$globalInstance->bindInstance(AuthenticationSchemeRegistry::class, new AuthenticationSchemeRegistry());
            }

            $appBuilder->withComponent(new AuthenticationComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(AuthenticationComponent::class)
            ->withScheme($scheme, $isDefault);

        return $this;
    }

    /**
     * Adds a policy to the authority
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param AuthorizationPolicy $policy The policy to add
     * @return static For chaining
     */
    protected function withAuthorizationPolicy(IApplicationBuilder $appBuilder, AuthorizationPolicy $policy): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(AuthorizationComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the authorization policy registry here so that it can be used in the component
            if (!Container::$globalInstance->hasBinding(AuthorizationPolicyRegistry::class)) {
                Container::$globalInstance->bindInstance(AuthorizationPolicyRegistry::class, new AuthorizationPolicyRegistry());
            }

            $appBuilder->withComponent(new AuthorizationComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(AuthorizationComponent::class)
            ->withPolicy($policy);

        return $this;
    }

    /**
     * Adds a requirement handler to the authority
     *
     * @template TRequirement of object
     * @template TResource of ?object
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param class-string<TRequirement> $requirementType
     * @param IAuthorizationRequirementHandler<TRequirement, TResource> $requirementHandler
     * @return static For chaining
     */
    protected function withAuthorizationRequirementHandler(
        IApplicationBuilder $appBuilder,
        string $requirementType,
        IAuthorizationRequirementHandler $requirementHandler
    ): static {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(AuthorizationComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the authorization requirement handler registry here so that it can be used in the component
            if (!Container::$globalInstance->hasBinding(AuthorizationRequirementHandlerRegistry::class)) {
                Container::$globalInstance->bindInstance(
                    AuthorizationRequirementHandlerRegistry::class,
                    new AuthorizationRequirementHandlerRegistry()
                );
            }

            $appBuilder->withComponent(new AuthorizationComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(AuthorizationComponent::class)
            ->withRequirementHandler($requirementType, $requirementHandler);

        return $this;
    }

    /**
     * Registers the binder dispatcher to use
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param IBinderDispatcher $binderDispatcher The binder dispatcher to use
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withBinderDispatcher(IApplicationBuilder $appBuilder, IBinderDispatcher $binderDispatcher): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(BinderComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(
                new BinderComponent(
                    Container::$globalInstance
                ),
                0
            );
        }

        $appBuilder->getComponent(BinderComponent::class)
            ->withBinderDispatcher($binderDispatcher);

        return $this;
    }

    /**
     * Adds binders to the binder component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Binder|list<Binder> $binders The binder or list of binders to add
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withBinders(IApplicationBuilder $appBuilder, Binder|array $binders): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(BinderComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(
                new BinderComponent(
                    Container::$globalInstance
                ),
                0
            );
        }

        $appBuilder->getComponent(BinderComponent::class)
            ->withBinders($binders);

        return $this;
    }

    /**
     * Enables console command attributes
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withCommandAttributes(IApplicationBuilder $appBuilder): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(CommandComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the command registry here so that it can be used in the component
            if (!Container::$globalInstance->hasBinding(CommandRegistry::class)) {
                Container::$globalInstance->bindInstance(CommandRegistry::class, new CommandRegistry());
            }

            $appBuilder->withComponent(new CommandComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(CommandComponent::class)
            ->withAttributes();

        return $this;
    }

    /**
     * Adds console commands to the command component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Closure(CommandRegistry): void $callback The callback that takes in an instance of CommandRegistry to register commands to
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withCommands(IApplicationBuilder $appBuilder, Closure $callback): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(CommandComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the command registry here so that it can be used in the component
            if (!Container::$globalInstance->hasBinding(CommandRegistry::class)) {
                Container::$globalInstance->bindInstance(CommandRegistry::class, new CommandRegistry());
            }

            $appBuilder->withComponent(new CommandComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(CommandComponent::class)
            ->withCommands($callback);

        return $this;
    }

    /**
     * Adds a console callback that takes in the exception and the output, and writes messages/returns the status code
     *
     * @template T of Exception
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param class-string<T> $exceptionType The type of exception whose result factory we're registering
     * @param Closure(T, IOutput): void|Closure(T, IOutput): StatusCode|Closure(T, IOutput): int $callback The callback that takes in an exception and the output, and writes messages/returns the status code
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withConsoleExceptionOutputWriter(
        IApplicationBuilder $appBuilder,
        string $exceptionType,
        Closure $callback
    ): static {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(ExceptionHandlerComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new ExceptionHandlerComponent(Container::$globalInstance));
        }

        /** @psalm-suppress PossiblyInvalidArgument https://github.com/vimeo/psalm/issues/9747 - bug */
        $appBuilder->getComponent(ExceptionHandlerComponent::class)
            ->withConsoleOutputWriter($exceptionType, $callback);

        return $this;
    }

    /**
     * Registers all the built-in framework commands
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param list<string> $commandNamesToExclude The names of built-in commands to exclude
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withFrameworkCommands(IApplicationBuilder $appBuilder, array $commandNamesToExclude = []): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(CommandComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the command registry here so that it can be used in the component
            if (!Container::$globalInstance->hasBinding(CommandRegistry::class)) {
                Container::$globalInstance->bindInstance(CommandRegistry::class, new CommandRegistry());
            }

            $appBuilder->withComponent(new CommandComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(CommandComponent::class)
            ->withCommands(static function (CommandRegistry $commands) use ($commandNamesToExclude) {
                $commandBindings = [
                    new CommandBinding(new FlushFrameworkCachesCommand(), FlushFrameworkCachesCommandHandler::class),
                    new CommandBinding(new ServeCommand(), ServeCommandHandler::class),
                    new CommandBinding(new RouteListCommand(), RouteListCommandHandler::class)
                ];

                foreach ($commandBindings as $commandBinding) {
                    if (\in_array($commandBinding->command->name, $commandNamesToExclude, true)) {
                        continue;
                    }

                    $commands->registerCommand($commandBinding->command, $commandBinding->commandHandlerClassName);
                }
            });

        return $this;
    }

    /**
     * Adds global middleware bindings to the middleware component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param MiddlewareBinding|list<MiddlewareBinding> $middlewareBindings The middleware binding or list of bindings to add
     * @param int|null $priority The optional priority to apply to the middleware (lower number => higher priority)
     * @return static For chaining
     * @throws ResolutionException Thrown if there was a problem resolving dependencies
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withGlobalMiddleware(
        IApplicationBuilder $appBuilder,
        MiddlewareBinding|array $middlewareBindings,
        int $priority = null
    ): static {
        if (!$appBuilder->hasComponent(MiddlewareComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            // Bind the middleware collection here so that it can be used in the component
            Container::$globalInstance->hasBinding(MiddlewareCollection::class)
                ? Container::$globalInstance->resolve(MiddlewareCollection::class)
                : Container::$globalInstance->bindInstance(MiddlewareCollection::class, new MiddlewareCollection());
            $appBuilder->withComponent(new MiddlewareComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(MiddlewareComponent::class)
            ->withGlobalMiddleware($middlewareBindings, $priority);

        return $this;
    }

    /**
     * Adds a log level factory to the exception handler component
     *
     * @template T of Exception
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param class-string<T> $exceptionType The exception type whose factory we're registering
     * @param Closure(T): string $logLevelFactory The factory that takes in an instance of the exception and returns the PSR-3 log level
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withLogLevelFactory(
        IApplicationBuilder $appBuilder,
        string $exceptionType,
        Closure $logLevelFactory
    ): static {
        //Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(ExceptionHandlerComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new ExceptionHandlerComponent(Container::$globalInstance));
        }

        /** @psalm-suppress InvalidArgument https://github.com/vimeo/psalm/issues/9747 - bug */
        $appBuilder->getComponent(ExceptionHandlerComponent::class)
            ->withLogLevelFactory($exceptionType, $logLevelFactory);

        return $this;
    }

    /**
     * Adds modules to the app builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param IModule|list<IModule> $modules The module or list of modules to add
     * @return static For chaining
     */
    protected function withModules(IApplicationBuilder $appBuilder, IModule|array $modules): static
    {
        if ($modules instanceof IModule) {
            $modules = [$modules];
        }

        foreach ($modules as $module) {
            $appBuilder->withModule($module);
        }

        return $this;
    }

    /**
     * Adds object constraints to the object constraints component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Closure(ObjectConstraintsRegistryBuilder): void $callback The callback that takes in an instance of ObjectConstraintsRegistryBuilder to register object constraints to
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withObjectConstraints(IApplicationBuilder $appBuilder, Closure $callback): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(ValidationComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new ValidationComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(ValidationComponent::class)
            ->withObjectConstraints($callback);

        return $this;
    }

    /**
     * Adds a mapping of an exception type to problem details properties
     *
     * @template T of Exception
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param class-string<T> $exceptionType The type of exception whose response factory we're registering
     * @param string|null|Closure(T): string $type The optional problem details type, or a closure that takes in the exception and returns a type, or null
     * @param string|null|Closure(T): string $title The optional problem details title, or a closure that takes in the exception and returns a title, or null
     * @param string|null|Closure(T): string $detail The optional problem details detail, or a closure that takes in the exception and returns a detail, or null
     * @param HttpStatusCode|int|Closure(T): HttpStatusCode|Closure(T): int $status The optional problem details status, or a closure that takes in the exception and returns a type, or null
     * @param string|null|Closure(T): string $instance The optional problem details instance, or a closure that takes in the exception and returns an instance, or null
     * @param array|null|Closure(T): array $extensions The optional problem details extensions, or a closure that takes in the exception and returns an exception, or null
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withProblemDetails(
        IApplicationBuilder $appBuilder,
        string $exceptionType,
        string|Closure $type = null,
        string|Closure $title = null,
        string|Closure $detail = null,
        HttpStatusCode|int|Closure $status = HttpStatusCode::InternalServerError,
        string|Closure $instance = null,
        array|Closure $extensions = null
    ): static {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(ExceptionHandlerComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new ExceptionHandlerComponent(Container::$globalInstance));
        }

        /** @psalm-suppress PossiblyInvalidArgument https://github.com/vimeo/psalm/issues/9747 - bug */
        $appBuilder->getComponent(ExceptionHandlerComponent::class)
            ->withProblemDetails($exceptionType, $type, $title, $detail, $status, $instance, $extensions);

        return $this;
    }

    /**
     * Enables routing attributes
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withRouteAttributes(IApplicationBuilder $appBuilder): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(RouterComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new RouterComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(RouterComponent::class)
            ->withAttributes();

        return $this;
    }

    /**
     * Adds routes to the router component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Closure(RouteCollectionBuilder ): void $callback The callback that takes in an instance of RouteCollectionBuilder to register route builders to
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withRoutes(IApplicationBuilder $appBuilder, Closure $callback): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(RouterComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new RouterComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(RouterComponent::class)
            ->withRoutes($callback);

        return $this;
    }

    /**
     * Enables Aphiria validation attributes
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return static For chaining
     * @throws RuntimeException Thrown if the global instance of the container is not set
     */
    protected function withValidatorAttributes(IApplicationBuilder $appBuilder): static
    {
        // Note: We are violating DRY here just so that we don't have confusing methods for enabling this component
        if (!$appBuilder->hasComponent(ValidationComponent::class)) {
            if (!isset(Container::$globalInstance)) {
                throw new RuntimeException('Global container instance not set');
            }

            $appBuilder->withComponent(new ValidationComponent(Container::$globalInstance));
        }

        $appBuilder->getComponent(ValidationComponent::class)
            ->withAttributes();

        return $this;
    }
}
