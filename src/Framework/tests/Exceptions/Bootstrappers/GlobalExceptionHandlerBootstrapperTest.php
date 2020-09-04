<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Bootstrappers;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Framework\Exceptions\Bootstrappers\GlobalExceptionHandlerBootstrapper;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
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
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class GlobalExceptionHandlerBootstrapperTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private Logger $logger;
    private GlobalExceptionHandlerBootstrapper $bootstrapper;
    private IApiExceptionRenderer $apiExceptionRenderer;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->bootstrapper = new class($this->container) extends GlobalExceptionHandlerBootstrapper {
            private bool $isRunningInConsole = false;

            public function setIsRunningInConsole(bool $isRunningInConsole): void
            {
                $this->isRunningInConsole = $isRunningInConsole;
            }

            protected function isRunningInConsole(): bool
            {
                return $this->isRunningInConsole;
            }
        };
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testApiExceptionRendererIsCreatedAndBoundInHttpContext(): void
    {
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(4))
            ->method('bindInstance')
            ->with(IExceptionRenderer::class, $this->isInstanceOf(ProblemDetailsExceptionRenderer::class));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
    }

    public function testConsoleExceptionRendererIsCreatedAndBoundInConsoleContext(): void
    {
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(4))
            ->method('bindInstance')
            ->with(IExceptionRenderer::class, $this->isInstanceOf(ConsoleExceptionRenderer::class));
        $this->bootstrapper->setIsRunningInConsole(true);
        $this->bootstrapper->bootstrap();
    }

    public function testCustomApiExceptionRendererIsCreatedAndBoundInHttpContext(): void
    {
        $customApiExceptionRenderer = new class() implements IApiExceptionRenderer {
            public function createResponse(Exception $ex): IResponse
            {
                return new Response(200);
            }

            public function setRequest(IRequest $request): void
            {
                // Don't do anything
            }

            /**
             * @inheritDoc
             */
            public function setResponseFactory(IResponseFactory $responseFactory): void
            {
                // Don't do anything
            }

            /**
             * @inheritDoc
             */
            public function render(Exception $ex): void
            {
                // Don't do anything
            }
        };
        $customApiExceptionRendererType = \get_class($customApiExceptionRenderer);
        $config = self::getBaseConfig();
        $config['aphiria']['exceptions']['apiExceptionRenderer'] = $customApiExceptionRendererType;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->addBootstrapAssertions($customApiExceptionRendererType, 1, 2);
        $this->addLoggerAssertion(3);
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with($customApiExceptionRendererType)
            ->willReturn($customApiExceptionRenderer);
        $this->container->expects($this->at(5))
            ->method('bindInstance')
            ->with(IExceptionRenderer::class, $customApiExceptionRenderer);
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
    }

    public function testHttpExceptionResponseFactoryIsRegistered(): void
    {
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
        $this->apiExceptionRenderer->setRequest($this->createMock(IRequest::class));
        $this->apiExceptionRenderer->setResponseFactory($this->createMock(IResponseFactory::class));
        $exception = new HttpException($this->createMock(IResponse::class));
        $this->apiExceptionRenderer->render($exception);
    }

    public function testInvalidRequestBodyExceptionProblemDetailsMappingIsRegisteredAndUsesProblemDetailsIfConfigured(): void
    {
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
        $request = $this->createMock(IRequest::class);
        $this->apiExceptionRenderer->setRequest($request);
        $responseFactory = $this->createMock(IResponseFactory::class);
        $responseFactory->expects($this->once())
            ->method('createResponse')
            ->with($request, HttpStatusCodes::HTTP_BAD_REQUEST, null, $this->isInstanceOf(ProblemDetails::class))
            ->willReturn(new Response(HttpStatusCodes::HTTP_BAD_REQUEST));
        $this->apiExceptionRenderer->setResponseFactory($responseFactory);
        $exception = new InvalidRequestBodyException(['foo']);
        $response = $this->apiExceptionRenderer->createResponse($exception);
        $this->assertSame(HttpStatusCodes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testLogLevelFactoryIsCreatedAndBound(): void
    {
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(LogLevelFactory::class, $this->isInstanceOf(LogLevelFactory::class));
        $this->bootstrapper->bootstrap();
    }

    public function testLoggerSupportsStreamHandler(): void
    {
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
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
        $this->addBootstrapAssertions();
        $this->addLoggerAssertion();
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

    public function testIsRunningInConsoleDefaultsToTrue(): void
    {
        $bootstrapper = new class($this->container) extends GlobalExceptionHandlerBootstrapper {
            public function isRunningInConsole(): bool
            {
                return parent::isRunningInConsole();
            }
        };
        $this->assertTrue($bootstrapper->isRunningInConsole());
    }

    /**
     * Gets the base config that will be used for all tests
     *
     * @return array The base config that will be used for all tests
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
     * @param string|null $exceptionRendererType The type of exception renderer to mock, or null if using the default
     * @param int|null $apiExceptionRendererBindIndex The index to use in the container's mocked bindInstance() method for the API exception renderer
     * @param int|null $consoleExceptionRendererBindIndex The index to use in the container's mocked bindInstance() method for the console exception renderer
     */
    private function addBootstrapAssertions(
        string $exceptionRendererType = null,
        int $apiExceptionRendererBindIndex = 0,
        int $consoleExceptionRendererBindIndex = 1
    ): void {
        $exceptionRendererType = $exceptionRendererType ?? ProblemDetailsExceptionRenderer::class;
        $this->container->expects($this->at($apiExceptionRendererBindIndex))
            ->method('bindInstance')
            ->with(
                [IApiExceptionRenderer::class, $exceptionRendererType],
                $this->callback(function (IApiExceptionRenderer $apiExceptionRenderer) {
                    $this->apiExceptionRenderer = $apiExceptionRenderer;

                    return true;
                })
            );
        $this->container->expects($this->at($consoleExceptionRendererBindIndex))
            ->method('bindInstance')
            ->with(ConsoleExceptionRenderer::class, $this->isInstanceOf(ConsoleExceptionRenderer::class));
    }

    /**
     * Some tests will perform the logger assertion, and others will throw an exception before hand
     * So, allow us to programmatically add the assertion
     *
     * @param int $loggerBindIndex The index to use in the container's mocked bindInstance() method for loggers
     */
    private function addLoggerAssertion(int $loggerBindIndex = 2): void
    {
        $this->container->expects($this->at($loggerBindIndex))
            ->method('bindInstance')
            ->with(LoggerInterface::class, $this->callback(function (Logger $logger) {
                // Save this so we can do some assertions on it later
                $this->logger = $logger;

                return true;
            }));
    }
}
