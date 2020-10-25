<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Components;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
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

        $outputWriter = function (Exception $ex, IOutput $output): int {
            $output->writeln('foo');

            return 0;
        };
        $this->exceptionHandlerComponent->withConsoleOutputWriter(Exception::class, $outputWriter);
        $this->exceptionHandlerComponent->build();
        $consoleExceptionHandler->render(new Exception());
    }

    public function testBuildWithExceptionProblemDetailsRegistersProblemDetails(): void
    {
        $apiExceptionRenderer = new ProblemDetailsExceptionRenderer();
        $request = $this->createMock(IRequest::class);
        $responseFactory = $this->createMock(IResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($request, 400, null, new ProblemDetails('type', 'title', 'detail', 400, 'instance', ['foo' => 'bar']))
            ->willReturn(new Response(400));
        $apiExceptionRenderer->setResponseFactory($responseFactory);
        $apiExceptionRenderer->setRequest($request);
        $this->container->bindInstance(IApiExceptionRenderer::class, $apiExceptionRenderer);
        $this->exceptionHandlerComponent->withProblemDetails(
            Exception::class,
            'type',
            'title',
            'detail',
            400,
            'instance',
            ['foo' => 'bar']
        );
        $this->exceptionHandlerComponent->build();
        $apiExceptionRenderer->createResponse(new Exception());
    }

    public function testBuildWithLogLevelFactoryRegistersFactory(): void
    {
        $expectedException = new Exception();
        $factory = fn (Exception $ex): string => LogLevel::ALERT;
        $this->exceptionHandlerComponent->withLogLevelFactory(Exception::class, $factory);
        $this->exceptionHandlerComponent->build();
        $this->assertSame(LogLevel::ALERT, $this->logLevelFactory->createLogLevel($expectedException));
    }
}
