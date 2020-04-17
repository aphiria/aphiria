<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Bootstrappers;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\IExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ApiExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Framework\Exceptions\Bootstrappers\GlobalExceptionHandlerBootstrapper;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Tests the global exception handler bootstrapper
 */
class GlobalExceptionHandlerBootstrapperTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private Logger $logger;
    private GlobalExceptionHandlerBootstrapper $bootstrapper;

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

        // Set up the container calls that are universal
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with(ApiExceptionRenderer::class, $this->isInstanceOf(ApiExceptionRenderer::class));
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(ConsoleExceptionRenderer::class, $this->isInstanceOf(ConsoleExceptionRenderer::class));
    }

    public function testApiExceptionRendererIsCreatedAndBoundInHttpContext(): void
    {
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(IExceptionRenderer::class, $this->isInstanceOf(ApiExceptionRenderer::class));
        $this->bootstrapper->setIsRunningInConsole(false);
        $this->bootstrapper->bootstrap();
    }

    public function testConsoleExceptionRendererIsCreatedAndBoundInConsoleContext(): void
    {
        $this->addLoggerAssertion();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(IExceptionRenderer::class, $this->isInstanceOf(ConsoleExceptionRenderer::class));
        $this->bootstrapper->setIsRunningInConsole(true);
        $this->bootstrapper->bootstrap();
    }

    public function testLoggerSupportsStreamHandler(): void
    {
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported logging handler type foo');
        $config = self::getBaseConfig();
        $config['aphiria']['logging']['handlers'][] = [
            'type' => 'foo'
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->bootstrapper->bootstrap();
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
                    'useProblemDetails' => true
                ],
                'logging' => [
                    'handlers' => [],
                    'name' => 'app'
                ]
            ]
        ];
    }

    /**
     * Some tests will perform the logger assertion, and others will throw an exception before hand
     * So, allow us to programmatically add the assertion
     */
    private function addLoggerAssertion(): void
    {
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with(LoggerInterface::class, $this->callback(function (Logger $logger) {
                // Save this so we can do some assertions on it later
                $this->logger = $logger;

                return true;
            }));
    }
}
