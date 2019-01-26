<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Configuration\Tests;

use Opulence\Api\Exceptions\IExceptionHandler;
use Opulence\Configuration\Config;
use Opulence\Ioc\IContainer;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Routing\Matchers\IRouteMatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests the config
 */
class ConfigTest extends TestCase
{
    /** @var Config */
    private $config;

    public function setUp(): void
    {
        $paths = ['foo' => 'bar'];
        /** @var IExceptionHandler|MockObject $exceptionHandler */
        $exceptionHandler = $this->createMock(IExceptionHandler::class);
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var IContainer|MockObject $container */
        $container = $this->createMock(IContainer::class);
        /** @var IRouteMatcher|MockObject $routeMatcher */
        $routeMatcher = $this->createMock(IRouteMatcher::class);
        /** @var IContentNegotiator|MockObject $contentNegotiator */
        $contentNegotiator = $this->createMock(IContentNegotiator::class);

        $this->config = new Config(
            $paths,
            $exceptionHandler,
            $logger,
            $container,
            $routeMatcher,
            $contentNegotiator
        );
    }

    public function testGettingNonExistentValue(): void
    {
        $this->assertNull($this->config->get('foo', 'bar'));
        $this->assertEquals('baz', $this->config->get('foo', 'bar', 'baz'));
        $this->assertFalse($this->config->has('foo', 'bar'));
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedPaths = ['foo' => 'bar'];
        /** @var IExceptionHandler|MockObject $expectedExceptionHandler */
        $expectedExceptionHandler = $this->createMock(IExceptionHandler::class);
        /** @var LoggerInterface|MockObject $expectedLogger */
        $expectedLogger = $this->createMock(LoggerInterface::class);
        /** @var IContainer|MockObject $expectedContainer */
        $expectedContainer = $this->createMock(IContainer::class);
        /** @var IRouteMatcher|MockObject $expectedRouteMatcher */
        $expectedRouteMatcher = $this->createMock(IRouteMatcher::class);
        /** @var IContentNegotiator|MockObject $expectedContentNegotiator */
        $expectedContentNegotiator = $this->createMock(IContentNegotiator::class);

        $config = new Config(
            $expectedPaths,
            $expectedExceptionHandler,
            $expectedLogger,
            $expectedContainer,
            $expectedRouteMatcher,
            $expectedContentNegotiator
        );
        $this->assertSame($expectedPaths, $config->paths);
        $this->assertSame($expectedExceptionHandler, $config->exceptionHandler);
        $this->assertSame($expectedLogger, $config->logger);
        $this->assertSame($expectedContainer, $config->container);
        $this->assertSame($expectedRouteMatcher, $config->routeMatcher);
        $this->assertSame($expectedContentNegotiator, $config->contentNegotiator);
    }

    public function testSettingCategory(): void
    {
        $this->config->setCategory('foo', ['bar' => 'baz']);
        $this->assertEquals('baz', $this->config->get('foo', 'bar'));
        $this->assertTrue($this->config->has('foo', 'bar'));
        $this->config->setCategory('foo', ['dave' => 'young']);
        $this->assertEquals('young', $this->config->get('foo', 'dave'));
        $this->assertTrue($this->config->has('foo', 'dave'));
    }

    public function testSettingSingleSetting(): void
    {
        $this->config->set('foo', 'bar', 'baz');
        $this->assertEquals('baz', $this->config->get('foo', 'bar'));
        $this->assertTrue($this->config->has('foo', 'bar'));
        $this->config->set('foo', 'bar', 'blah');
        $this->assertEquals('blah', $this->config->get('foo', 'bar'));
        $this->assertTrue($this->config->has('foo', 'bar'));
    }
}
