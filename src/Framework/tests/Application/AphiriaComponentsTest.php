<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Application;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\IBinderDispatcher;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Application\AphiriaComponents;
use Aphiria\Framework\Console\Components\CommandComponent;
use Aphiria\Framework\DependencyInjection\Components\BinderComponent;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Framework\Routing\Components\RouterComponent;
use Aphiria\Framework\Validation\Components\ValidationComponent;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Closure;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;

class AphiriaComponentsTest extends TestCase
{
    private IApplicationBuilder|MockObject $appBuilder;

    protected function setUp(): void
    {
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
        Container::$globalInstance = new Container();
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testWithBinderDispatcherRegisterBinderDispatcherToComponent(): void
    {
        $binderDispatcher = $this->createMock(IBinderDispatcher::class);
        $expectedComponent = $this->createMock(BinderComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withBinderDispatcher')
            ->with($binderDispatcher);
        $this->appBuilder->method('hasComponent')
            ->with(BinderComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(BinderComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, IBinderDispatcher $binderDispatcher): void
            {
                $this->withBinderDispatcher($appBuilder, $binderDispatcher);
            }
        };
        $component->build($this->appBuilder, $binderDispatcher);
    }

    public function testWithBinderDispatcherRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(BinderComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(BinderComponent::class), 0);
        $this->appBuilder->method('getComponent')
            ->with(BinderComponent::class)
            ->willReturn($this->createMock(BinderComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, IBinderDispatcher $binderDispatcher): void
            {
                $this->withBinderDispatcher($appBuilder, $binderDispatcher);
            }
        };
        $component->build($this->appBuilder, $this->createMock(IBinderDispatcher::class));
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithBinderDispatcherWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, IBinderDispatcher $binderDispatcher): void
            {
                $this->withBinderDispatcher($appBuilder, $binderDispatcher);
            }
        };
        $component->build($this->appBuilder, $this->createMock(IBinderDispatcher::class));
    }

    public function testWithBindersRegistersBindersToComponent(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedComponent = $this->createMock(BinderComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withBinders')
            ->with($binder);
        $this->appBuilder->method('hasComponent')
            ->with(BinderComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(BinderComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Binder $binder): void
            {
                $this->withBinders($appBuilder, $binder);
            }
        };
        $component->build($this->appBuilder, $binder);
    }

    public function testWithBindersRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(BinderComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(BinderComponent::class), 0);
        $this->appBuilder->method('getComponent')
            ->with(BinderComponent::class)
            ->willReturn($this->createMock(BinderComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Binder $binder): void
            {
                $this->withBinders($appBuilder, $binder);
            }
        };
        $component->build($this->appBuilder, $this->createMock(Binder::class));
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithBindersWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Binder $binder): void
            {
                $this->withBinders($appBuilder, $binder);
            }
        };
        $component->build($this->appBuilder, $this->createMock(Binder::class));
    }

    public function testWithCommandAttributesConfiguresComponentToHaveAttributes(): void
    {
        $expectedComponent = $this->createMock(CommandComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withAttributes');
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withCommandAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithCommandAttributesRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(CommandComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($this->createMock(CommandComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withCommandAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithCommandAttributesWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withCommandAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithCommandsConfiguresComponentToHaveCommands(): void
    {
        $callback = fn (CommandRegistry $commands): mixed => null;
        $expectedComponent = $this->createMock(CommandComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withCommands')
            ->with($callback);
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withCommands($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, $callback);
    }

    public function testWithCommandsRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(CommandComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($this->createMock(CommandComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withCommands($appBuilder, $callback);
            }
        };
        $callback = fn (CommandRegistry $commands): mixed => null;
        $component->build($this->appBuilder, $callback);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithCommandsWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withCommands($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, fn (CommandRegistry $commands): mixed => null);
    }

    public function testWithComponentAddsComponentToAppBuilder(): void
    {
        $component = $this->createMock(IComponent::class);
        $this->appBuilder->expects($this->once())
            ->method('withComponent')
            ->with($component);
        $module = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, IComponent $component): void
            {
                $this->withComponent($appBuilder, $component);
            }
        };
        $module->build($this->appBuilder, $component);
    }

    public function testWithConsoleExceptionOutputWriterConfiguresComponentToHaveWriter(): void
    {
        $outputWriter = function (Exception $ex, IOutput $output): int {
            $output->writeln('foo');

            return 1;
        };
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withConsoleOutputWriter')
            ->with(Exception::class, $outputWriter);
        $this->appBuilder->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            /**
             * @template T of Exception
             * @param IApplicationBuilder $appBuilder
             * @param class-string<T> $exceptionType
             * @param Closure(T, IOutput) $callback
             */
            public function build(IApplicationBuilder $appBuilder, string $exceptionType, Closure $callback): void
            {
                $this->withConsoleExceptionOutputWriter($appBuilder, $exceptionType, $callback);
            }
        };
        $component->build($this->appBuilder, Exception::class, $outputWriter);
    }

    public function testWithConsoleExceptionOutputWriterRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(ExceptionHandlerComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($this->createMock(ExceptionHandlerComponent::class));
        $component = new class() {
            use AphiriaComponents;

            /**
             * @param IApplicationBuilder $appBuilder
             */
            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withConsoleExceptionOutputWriter($appBuilder, Exception::class, fn (Exception $ex, IOutput $output): mixed => null);
            }
        };
        $component->build($this->appBuilder);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithConsoleExceptionOutputWriterWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            /**
             * @param IApplicationBuilder $appBuilder
             */
            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withConsoleExceptionOutputWriter($appBuilder, Exception::class, fn (Exception $ex, IOutput $output): mixed => null);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithFrameworkCommandsConfiguresComponentToHaveCommands(): void
    {
        $expectedComponent = $this->createMock(CommandComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withCommands')
            ->with($this->callback(function (Closure $callback) {
                $commands = new CommandRegistry();
                $callback($commands);

                return $commands->tryGetCommand('framework:flushcaches', $flushCommandHandler)
                    && $commands->tryGetCommand('app:serve', $serveCommandHandler);
            }));
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedComponent);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration([
            'aphiria' => ['api' => ['localhostRouterPath' => '/router']]
        ]));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withFrameworkCommands($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithFrameworkCommandsExcludesSpecificOnes(): void
    {
        $expectedComponent = $this->createMock(CommandComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withCommands')
            ->with($this->callback(function (Closure $callback) {
                $commands = new CommandRegistry();
                $callback($commands);

                return $commands->tryGetCommand('framework:flushcaches', $flushCommandHandler)
                    && !$commands->tryGetCommand('app:serve', $serveCommandHandler);
            }));
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($expectedComponent);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration([
            'aphiria' => ['api' => ['localhostRouterPath' => '/router']]
        ]));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withFrameworkCommands($appBuilder, ['app:serve']);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithFrameworkCommandsRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(CommandComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(CommandComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(CommandComponent::class)
            ->willReturn($this->createMock(CommandComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withFrameworkCommands($appBuilder);
            }
        };
        $component->build($this->appBuilder);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithFrameworkCommandsWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withFrameworkCommands($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithGlobalMiddlewareConfiguresComponentToHaveMiddleware(): void
    {
        $middleware = new class() {
        };
        $middlewareBinding = new MiddlewareBinding($middleware::class);
        $expectedComponent = $this->createMock(MiddlewareComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withGlobalMiddleware')
            ->with($middlewareBinding, 1);
        $this->appBuilder->method('hasComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(
                IApplicationBuilder $appBuilder,
                MiddlewareBinding $middlewareBinding,
                int $priority
            ): void {
                $this->withGlobalMiddleware($appBuilder, $middlewareBinding, $priority);
            }
        };
        $component->build($this->appBuilder, $middlewareBinding, 1);
    }

    public function testWithGlobalMiddlewareRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(MiddlewareComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($this->createMock(MiddlewareComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, MiddlewareBinding $middlewareBinding): void
            {
                $this->withGlobalMiddleware($appBuilder, $middlewareBinding);
            }
        };
        $middleware = new class() {
        };
        $component->build($this->appBuilder, new MiddlewareBinding($middleware::class));
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithGlobalMiddlewareRegistersComponentIfItIsNotRegisteredYetAndUsesBoundMiddlewareCollection(): void
    {
        Container::$globalInstance?->bindInstance(MiddlewareCollection::class, new MiddlewareCollection());
        $this->appBuilder->method('hasComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(MiddlewareComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(MiddlewareComponent::class)
            ->willReturn($this->createMock(MiddlewareComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, MiddlewareBinding $middlewareBinding): void
            {
                $this->withGlobalMiddleware($appBuilder, $middlewareBinding);
            }
        };
        $middleware = new class() {
        };
        $component->build($this->appBuilder, new MiddlewareBinding($middleware::class));
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithGlobalMiddlewareWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $middleware = new class() {
        };
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, MiddlewareBinding $middlewareBinding): void
            {
                $this->withGlobalMiddleware($appBuilder, $middlewareBinding);
            }
        };
        $component->build($this->appBuilder, new MiddlewareBinding($middleware::class));
    }

    public function testWithLogLevelFactoryConfiguresComponentToHaveFactory(): void
    {
        $logLevelFactory = fn (Exception $ex): string => LogLevel::ALERT;
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withLogLevelFactory')
            ->with(Exception::class, $logLevelFactory);
        $this->appBuilder->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            /**
             * @template T of Exception
             * @param IApplicationBuilder $appBuilder
             * @param class-string<T> $exceptionType
             * @param Closure(T): string $logLevelFactory
             */
            public function build(
                IApplicationBuilder $appBuilder,
                string $exceptionType,
                Closure $logLevelFactory
            ): void {
                $this->withLogLevelFactory($appBuilder, $exceptionType, $logLevelFactory);
            }
        };
        $component->build($this->appBuilder, Exception::class, $logLevelFactory);
    }

    public function testWithLogLevelFactoryRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(ExceptionHandlerComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($this->createMock(ExceptionHandlerComponent::class));
        $component = new class() {
            use AphiriaComponents;

            /**
             * @param IApplicationBuilder $appBuilder
             */
            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withLogLevelFactory($appBuilder, Exception::class, fn (Exception $ex): string => LogLevel::ALERT);
            }
        };
        $component->build($this->appBuilder);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithLogLevelFactoryWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            /**
             * @param IApplicationBuilder $appBuilder
             */
            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withLogLevelFactory($appBuilder, Exception::class, fn (Exception $ex): string => LogLevel::ALERT);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithModulesAddsMultipleModulesToAppBuilder(): void
    {
        $modules = [$this->createMock(IModule::class), $this->createMock(IModule::class)];
        $this->appBuilder->method('withModule')
            ->withConsecutive([$modules[0]], [$modules[1]]);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, array $modules): void
            {
                $this->withModules($appBuilder, $modules);
            }
        };
        $component->build($this->appBuilder, $modules);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithModulesAddsSingleModuleToAppBuilder(): void
    {
        $module = $this->createMock(IModule::class);
        $this->appBuilder->expects($this->once())
            ->method('withModule')
            ->with($module);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, IModule $module): void
            {
                $this->withModules($appBuilder, $module);
            }
        };
        $component->build($this->appBuilder, $module);
    }

    public function testWithObjectConstraintsConfiguresComponentToHaveObjectConstraints(): void
    {
        $callback = fn (ObjectConstraintsRegistry $objectConstraints): mixed => null;
        $expectedComponent = $this->createMock(ValidationComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withObjectConstraints')
            ->with($callback);
        $this->appBuilder->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withObjectConstraints($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, $callback);
    }

    public function testWithObjectConstraintsRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(ValidationComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($this->createMock(ValidationComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withObjectConstraints($appBuilder, $callback);
            }
        };
        $factory = fn (ObjectConstraintsRegistry $objectConstraints): mixed => null;
        $component->build($this->appBuilder, $factory);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithObjectConstraintsWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withObjectConstraints($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, fn (ObjectConstraintsRegistry $objectConstraints): mixed => null);
    }

    public function testWithProblemDetailsConfiguresComponentToHaveProblemDetails(): void
    {
        $expectedComponent = $this->createMock(ExceptionHandlerComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withProblemDetails')
            ->with(Exception::class, 'type', 'title', 'detail', 400, 'instance', ['foo' => 'bar']);
        $this->appBuilder->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            /**
             * @param IApplicationBuilder $appBuilder
             * @param class-string $exceptionType
             * @param string|Closure|null $type
             * @param string|Closure|null $title
             * @param string|Closure|null $detail
             * @param int|Closure $status
             * @param string|Closure|null $instance
             * @param array|Closure|null $extensions
             */
            public function build(
                IApplicationBuilder $appBuilder,
                string $exceptionType,
                string|Closure $type = null,
                string|Closure$title = null,
                string|Closure$detail = null,
                int|Closure $status = HttpStatusCodes::INTERNAL_SERVER_ERROR,
                string|Closure$instance = null,
                array|Closure $extensions = null
            ): void {
                $this->withProblemDetails($appBuilder, $exceptionType, $type, $title, $detail, $status, $instance, $extensions);
            }
        };
        $component->build($this->appBuilder, Exception::class, 'type', 'title', 'detail', 400, 'instance', ['foo' => 'bar']);
    }

    public function testWithProblemDetailsRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(ExceptionHandlerComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(ExceptionHandlerComponent::class)
            ->willReturn($this->createMock(ExceptionHandlerComponent::class));
        $component = new class() {
            use AphiriaComponents;

            /**
             * @param IApplicationBuilder $appBuilder
             * @param class-string $exceptionType
             * @param string|Closure|null $type
             * @param string|Closure|null $title
             * @param string|Closure|null $detail
             * @param int|Closure $status
             * @param string|Closure|null $instance
             * @param array|Closure|null $extensions
             */
            public function build(
                IApplicationBuilder $appBuilder,
                string $exceptionType,
                string|Closure $type = null,
                string|Closure $title = null,
                string|Closure $detail = null,
                int|Closure $status = HttpStatusCodes::INTERNAL_SERVER_ERROR,
                string|Closure $instance = null,
                array|Closure $extensions = null
            ): void {
                $this->withProblemDetails($appBuilder, $exceptionType, $type, $title, $detail, $status, $instance, $extensions);
            }
        };
        $component->build($this->appBuilder, Exception::class, 'type', 'title', 'detail', 400, 'instance', ['foo' => 'bar']);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithProblemDetailssWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withProblemDetails($appBuilder, Exception::class);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithRouteAttributesConfiguresComponentToHaveAttributes(): void
    {
        $expectedComponent = $this->createMock(RouterComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withAttributes');
        $this->appBuilder->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withRouteAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithRouteAttributesRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(RouterComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($this->createMock(RouterComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withRouteAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithRouteAttributesWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withRouteAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithRoutesConfiguresComponentToHaveRoutes(): void
    {
        $callback = fn (RouteCollectionBuilder $routeBuilders): mixed => null;
        $expectedComponent = $this->createMock(RouterComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withRoutes')
            ->with($callback);
        $this->appBuilder->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withRoutes($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, $callback);
    }

    public function testWithRoutesRegistersComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(RouterComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(RouterComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(RouterComponent::class)
            ->willReturn($this->createMock(RouterComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withRoutes($appBuilder, $callback);
            }
        };
        $callback = fn (RouteCollectionBuilder $routeBuilders): mixed => null;
        $component->build($this->appBuilder, $callback);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithRoutesWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder, Closure $callback): void
            {
                $this->withRoutes($appBuilder, $callback);
            }
        };
        $component->build($this->appBuilder, fn (RouteCollectionBuilder $routeBuilders): mixed => null);
    }

    public function testWithValidatorAttributesConfiguresComponentToHaveAttributes(): void
    {
        $expectedComponent = $this->createMock(ValidationComponent::class);
        $expectedComponent->expects($this->once())
            ->method('withAttributes');
        $this->appBuilder->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(true);
        $this->appBuilder->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($expectedComponent);
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withValidatorAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }

    public function testWithValidatorAttributesComponentIfItIsNotRegisteredYet(): void
    {
        $this->appBuilder->method('hasComponent')
            ->with(ValidationComponent::class)
            ->willReturn(false);
        $this->appBuilder->method('withComponent')
            ->with($this->isInstanceOf(ValidationComponent::class));
        $this->appBuilder->method('getComponent')
            ->with(ValidationComponent::class)
            ->willReturn($this->createMock(ValidationComponent::class));
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withValidatorAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testWithValidatorAttributesWithoutGlobalContainerInstanceSetThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Global container instance not set');
        Container::$globalInstance = null;
        $component = new class() {
            use AphiriaComponents;

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->withValidatorAttributes($appBuilder);
            }
        };
        $component->build($this->appBuilder);
    }
}
