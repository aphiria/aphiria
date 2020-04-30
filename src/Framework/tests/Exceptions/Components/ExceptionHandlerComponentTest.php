<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Components;

use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\ApiExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\IResponseWriter;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class ExceptionHandlerComponentTest extends TestCase
{
    private IContainer $container;
    private ExceptionHandlerComponent $exceptionHandlerComponent;
    private LogLevelFactory $logLevelFactory;

    protected function setUp(): void
    {
        $this->logLevelFactory = new LogLevelFactory();
        $this->container = new Container();
        $this->container->bindInstance(LogLevelFactory::class, $this->logLevelFactory);
        $this->exceptionHandlerComponent = new ExceptionHandlerComponent($this->container);
    }

    protected function tearDown(): void
    {
        // Remove the global instance so it doesn't impact other tests
        Container::$globalInstance = null;
    }

    public function testBuildWithConsoleOutputWriterRegistersCallback(): void
    {
        $output = $this->createMock(IOutput::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('foo');
        // Make sure the renderer doesn't cause the app to exit
        $consoleExceptionHandler = new ConsoleExceptionRenderer($output, false);
        $this->container->bindInstance(ConsoleExceptionRenderer::class, $consoleExceptionHandler);

        $outputWriter = function (Exception $ex, IOutput $output) {
            $output->writeln('foo');

            return 0;
        };
        $this->exceptionHandlerComponent->withConsoleOutputWriter(Exception::class, $outputWriter);
        $this->exceptionHandlerComponent->build();
        $consoleExceptionHandler->render(new Exception());
    }

    public function testBuildWithHttpResponseFactoryRegistersFactory(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $responseWriter = $this->createMock(IResponseWriter::class);
        $responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $httpExceptionHandler = new ApiExceptionRenderer(true, null, null, $responseWriter);
        // Need to make sure the content negotiator is set so that the factory is invoked
        $httpExceptionHandler->setResponseFactory($this->createMock(IResponseFactory::class));
        $httpExceptionHandler->setRequest($this->createMock(IRequest::class));
        $this->container->bindInstance(ApiExceptionRenderer::class, $httpExceptionHandler);

        $factory = fn (Exception $ex) => $expectedResponse;
        $this->exceptionHandlerComponent->withHttpResponseFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $httpExceptionHandler->render(new Exception());
    }

    public function testBuildWithLogLevelFactoryRegistersFactory(): void
    {
        $expectedException = new Exception();
        $factory = fn (Exception $ex) => LogLevel::ALERT;
        $this->exceptionHandlerComponent->withLogLevelFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->assertEquals(LogLevel::ALERT, $this->logLevelFactory->createLogLevel($expectedException));
    }
}
