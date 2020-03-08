<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Exceptions\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilderProxy;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Defines the exception handler builder proxy
 */
class ExceptionHandlerBuilderProxyTest extends TestCase
{
    private ExceptionHandlerBuilderProxy $exceptionHandlerBuilderProxy;
    /** @var ExceptionHandlerBuilder|MockObject */
    private ExceptionHandlerBuilder $exceptionHandlerBuilder;

    protected function setUp(): void
    {
        $this->exceptionHandlerBuilder = $this->createMock(ExceptionHandlerBuilder::class);
        $this->exceptionHandlerBuilderProxy = new ExceptionHandlerBuilderProxy(
            fn () => $this->exceptionHandlerBuilder
        );
    }

    public function testBuildRegistersLogLevelFactoryToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedLogLevelFactory = fn (Exception $ex) => LogLevel::ALERT;
        $this->exceptionHandlerBuilder->expects($this->at(0))
            ->method('withLogLevelFactory')
            ->with(Exception::class, $expectedLogLevelFactory);
        $this->exceptionHandlerBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->exceptionHandlerBuilderProxy->withLogLevelFactory(Exception::class, $expectedLogLevelFactory);
        $this->exceptionHandlerBuilderProxy->build($expectedAppBuilder);
    }

    public function testBuildRegistersResponseFactoryToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedResponseFactory = fn (Exception $ex) => $this->createMock(IHttpResponseMessage::class);
        $this->exceptionHandlerBuilder->expects($this->at(0))
            ->method('withResponseFactory')
            ->with(Exception::class, $expectedResponseFactory);
        $this->exceptionHandlerBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->exceptionHandlerBuilderProxy->withResponseFactory(Exception::class, $expectedResponseFactory);
        $this->exceptionHandlerBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(ExceptionHandlerBuilder::class, $this->exceptionHandlerBuilderProxy->getProxiedType());
    }
}
