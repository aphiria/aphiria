<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Bootstrappers;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Framework\Exceptions\Bootstrappers\GlobalExceptionHandlerBootstrapper;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Exception;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class GlobalExceptionHandlerBootstrapperTest extends TestCase
{
    private IApiExceptionRenderer $apiExceptionRenderer;
    private GlobalExceptionHandlerBootstrapper $bootstrapper;
    private IContainer&MockObject $container;
    private Logger $logger;
    private bool $setsExceptionAndErrorHandler = false;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->bootstrapper = new class ($this->container) extends GlobalExceptionHandlerBootstrapper {
            public bool $isRunningInConsole = false;
        };
        GlobalConfiguration::resetConfigurationSources();
    }

    protected function tearDown(): void
    {
        if ($this->setsExceptionAndErrorHandler) {
            \restore_exception_handler();
            \restore_error_handler();
        }
    }

    public function testApiExceptionRendererIsCreatedAndBoundInHttpContext(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testConsoleExceptionRendererIsCreatedAndBoundInConsoleContext(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions(ConsoleExceptionRenderer::class);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->isRunningInConsole = true;
        $this->bootstrapper->bootstrap();
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testCustomApiExceptionRendererIsCreatedAndBoundInHttpContext(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $customApiExceptionRenderer = new class () implements IApiExceptionRenderer {
            public function createResponse(Exception $ex): IResponse
            {
                return new Response(200);
            }

            /**
             * @inheritdoc
             */
            public function render(Exception $ex): void
            {
                // Don't do anything
            }
        };
        $customApiExceptionRendererType = $customApiExceptionRenderer::class;
        $config = self::getBaseConfig();
        $config['aphiria']['exceptions']['apiExceptionRenderer'] = $customApiExceptionRendererType;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->addBootstrapAssertions($customApiExceptionRendererType);
        $this->container->method('resolve')
            ->willReturnMap([
                [$customApiExceptionRendererType, $customApiExceptionRenderer]
            ]);
        $this->bootstrapper->isRunningInConsole = false;
        $this->bootstrapper->bootstrap();
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testHttpExceptionResponseFactoryIsRegistered(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
        $this->apiExceptionRenderer->request = $this->createMock(IRequest::class);
        $this->apiExceptionRenderer->responseFactory = $this->createMock(IResponseFactory::class);
        $exception = new HttpException($this->createMock(IResponse::class));
        $this->apiExceptionRenderer->render($exception);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testInvalidExceptionRendererThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exception renderer must implement ' . IExceptionRenderer::class);
        $config = self::getBaseConfig();
        $config['aphiria']['exceptions']['apiExceptionRenderer'] = self::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        /** @psalm-suppress InvalidArgument Purposely testing an invalid argument */
        $this->addBootstrapAssertions(self::class);
        $this->container->method('resolve')
            ->with(self::class)
            ->willReturn($this);
        $this->bootstrapper->isRunningInConsole = false;
        $this->bootstrapper->bootstrap();
    }

    public function testInvalidRequestBodyExceptionProblemDetailsMappingIsRegisteredAndUsesProblemDetailsIfConfigured(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
        $request = $this->createMock(IRequest::class);
        $this->apiExceptionRenderer->request = $request;
        $responseFactory = $this->createMock(IResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($request, HttpStatusCode::BadRequest->value, null, $this->isInstanceOf(ProblemDetails::class))
            ->willReturn(new Response(HttpStatusCode::BadRequest));
        $this->apiExceptionRenderer->responseFactory = $responseFactory;
        $exception = new InvalidRequestBodyException(['foo']);
        $response = $this->apiExceptionRenderer->createResponse($exception);
        $this->assertSame(HttpStatusCode::BadRequest, $response->statusCode);
    }

    public function testIsRunningInConsoleDefaultsToTrue(): void
    {
        $bootstrapper = new class ($this->container) extends GlobalExceptionHandlerBootstrapper {
            public bool $isRunningInConsole {
                get => parent::$isRunningInConsole;
            }
        };
        $this->assertTrue($bootstrapper->isRunningInConsole);
    }

    public function testLoggerSupportsStreamHandler(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions();
        $config = self::getBaseConfig();
        $config['aphiria']['logging']['handlers'][] = [
            'type' => StreamHandler::class,
            'path' => '/path',
            'level' => LogLevel::DEBUG
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->bootstrapper->bootstrap();
        $this->assertCount(1, $this->logger->getHandlers());
        $this->assertInstanceOf(StreamHandler::class, $this->logger->getHandlers()[0]);
    }

    public function testLoggerSupportsSysLogHandler(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions();
        $config = self::getBaseConfig();
        $config['aphiria']['logging']['handlers'][] = [
            'type' => SyslogHandler::class,
            'ident' => 'app',
            'level' => LogLevel::DEBUG
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->bootstrapper->bootstrap();
        $this->assertCount(1, $this->logger->getHandlers());
        $this->assertInstanceOf(SyslogHandler::class, $this->logger->getHandlers()[0]);
    }

    public function testLoggerWithUnsupportedHandlerThrowsException(): void
    {
        $this->addBootstrapAssertions();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported logging handler type foo');
        $config = self::getBaseConfig();
        $config['aphiria']['logging']['handlers'][] = [
            'type' => 'foo'
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->bootstrapper->bootstrap();
    }

    public function testLogLevelFactoryIsCreatedAndBound(): void
    {
        $this->setsExceptionAndErrorHandler = true;
        $this->addBootstrapAssertions();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->bootstrap();
        // Dummy assertion
        $this->assertTrue(true);
    }

    /**
     * Gets the base config that will be used for all tests
     *
     * @return array<string, mixed> The base config that will be used for all tests
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'exceptions' => [
                    'apiExceptionRenderer' => ProblemDetailsExceptionRenderer::class
                ],
                'logging' => [
                    'handlers' => [],
                    'name' => 'app'
                ]
            ]
        ];
    }

    /**
     * Adds assertions for tests that call bootstrap()
     *
     * @param class-string<IExceptionRenderer> $expectedExceptionRendererType The type of exception renderer to mock
     */
    private function addBootstrapAssertions(
        string $expectedExceptionRendererType = ProblemDetailsExceptionRenderer::class
    ): void {
        /**
         * Hack alert
         *
         * Checking that a void method was called with certain parameters, and capturing those parameters, is weirdly
         * difficult in PHPUnit.  As a result, this mock isn't even doing anything with the type  parameter.
         */
        $this->container->method('bindInstance')
            ->with($this->anything(), $this->callback(function (mixed $actualInstance) use ($expectedExceptionRendererType): bool {
                // The problem details renderer is always bound, even in console contexts.  So, check whether or not the renderer is that or the expected one.
                if ($actualInstance instanceof ProblemDetailsExceptionRenderer) {
                    $this->apiExceptionRenderer = $actualInstance;

                    return true;
                }

                if ($actualInstance::class === $expectedExceptionRendererType) {
                    return true;
                }

                if ($actualInstance instanceof ConsoleExceptionRenderer) {
                    return true;
                }

                if ($actualInstance instanceof Logger) {
                    $this->logger = $actualInstance;

                    return true;
                }

                if ($actualInstance instanceof LogLevelFactory) {
                    return true;
                }

                if ($actualInstance instanceof GlobalExceptionHandler) {
                    return true;
                }

                return false;
            }));
    }
}
