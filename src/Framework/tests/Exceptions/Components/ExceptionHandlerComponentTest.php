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

use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\Http\HttpExceptionHandler;
use Aphiria\Exceptions\LogLevelRegistry;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseWriter;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Tests the exception handler component
 */
class ExceptionHandlerComponentTest extends TestCase
{
    private IContainer $container;
    private ExceptionHandlerComponent $exceptionHandlerComponent;
    private LogLevelRegistry $logLevels;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bindInstance(LogLevelRegistry::class, $this->logLevels = new LogLevelRegistry());
        $this->exceptionHandlerComponent = new ExceptionHandlerComponent($this->container);
    }

    protected function tearDown(): void
    {
        // Remove the global instance so it doesn't impact other tests
        Container::$globalInstance = null;
    }

    public function testInitializeWithLogLevelFactoryRegistersFactory(): void
    {
        $factory = fn (Exception $ex) => LogLevel::ALERT;
        $this->exceptionHandlerComponent->withLogLevelFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->assertSame(LogLevel::ALERT, $this->logLevels->getLogLevel(new Exception));
    }

    public function testInitializeWithResponseFactoryRegistersFactory(): void
    {
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $responseWriter = $this->createMock(IResponseWriter::class);
        $responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($expectedResponse);
        $httpExceptionHandler = new HttpExceptionHandler(true, null, null, $responseWriter);
        // Need to make sure the content negotiator is set so that the factory is invoked
        $httpExceptionHandler->setResponseFactory($this->createMock(IResponseFactory::class));
        $httpExceptionHandler->setRequest($this->createMock(IHttpRequestMessage::class));
        $this->container->bindInstance(HttpExceptionHandler::class, $httpExceptionHandler);

        $factory = fn (Exception $ex) => $expectedResponse;
        $this->exceptionHandlerComponent->withResponseFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $httpExceptionHandler->handle(new Exception());
    }
}
