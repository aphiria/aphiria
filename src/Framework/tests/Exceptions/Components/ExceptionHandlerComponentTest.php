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
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Framework\Exceptions\Console\ConsoleExceptionRenderer;
use Aphiria\Framework\Exceptions\Http\HttpExceptionRenderer;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\IResponseWriter;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Tests the exception handler component
 */
class ExceptionHandlerComponentTest extends TestCase
{
    private IContainer $container;
    private ExceptionHandlerComponent $exceptionHandlerComponent;
    private GlobalExceptionHandler $globalExceptionHandler;
    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->globalExceptionHandler = new GlobalExceptionHandler(
            $this->createMock(IExceptionRenderer::class),
            $this->logger
        );
        $this->container = new Container();
        $this->container->bindInstance(GlobalExceptionHandler::class, $this->globalExceptionHandler);
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
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $responseWriter = $this->createMock(IResponseWriter::class);
        $responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $httpExceptionHandler = new HttpExceptionRenderer(true, null, null, $responseWriter);
        // Need to make sure the content negotiator is set so that the factory is invoked
        $httpExceptionHandler->setResponseFactory($this->createMock(IResponseFactory::class));
        $httpExceptionHandler->setRequest($this->createMock(IHttpRequestMessage::class));
        $this->container->bindInstance(HttpExceptionRenderer::class, $httpExceptionHandler);

        $factory = fn (Exception $ex) => $expectedResponse;
        $this->exceptionHandlerComponent->withHttpResponseFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $httpExceptionHandler->render(new Exception());
    }

    public function testBuildWithLogLevelFactoryRegistersFactory(): void
    {
        $expectedException = new Exception;
        $this->logger->expects($this->once())
            ->method('alert')
            ->with($expectedException);
        $factory = fn (Exception $ex) => LogLevel::ALERT;
        $this->exceptionHandlerComponent->withLogLevelFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->globalExceptionHandler->handleException($expectedException);
    }
}
