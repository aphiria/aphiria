<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Binders;

use Aphiria\Api\Controllers\IRequestParameterDeserializer;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Binders\ControllerBinder;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ControllerBinderTest extends TestCase
{
    private ControllerBinder $binder;
    private IContainer&MockInterface $container;

    protected function setUp(): void
    {
        $this->container = Mockery::spy(IContainer::class);
        $this->binder = new ControllerBinder();

        // Set up some universal mocks
        $this->container->shouldReceive('resolve')
            ->with(IValidator::class)
            ->andReturn($this->createMock(IValidator::class));
        $this->container->shouldReceive('resolve')
            ->with(IErrorMessageInterpolator::class)
            ->andReturn($this->createMock(IErrorMessageInterpolator::class));
        $this->container->shouldReceive('resolve')
            ->with(IContentNegotiator::class)
            ->andReturn($this->createMock(IContentNegotiator::class));
        $this->container->shouldReceive('resolve')
            ->with(IBodyDeserializer::class)
            ->andReturn($this->createMock(IBodyDeserializer::class));
        $this->container->shouldReceive('resolve')
            ->with(IResponseFactory::class)
            ->andReturn($this->createMock(IResponseFactory::class));

        // Set up the date format config
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(['aphiria' => ['serialization' => ['dateFormat' => 'Y-m-d']]]));
    }

    public function testRequestParameterDeserializerIsBound(): void
    {
        $this->container->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, $this->isInstanceOf(IRequestParameterDeserializer::class));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRouteActionInvokerIsBound(): void
    {
        $this->container->shouldReceive('bindInstance')
            ->with(IRouteActionInvoker::class, $this->isInstanceOf(RouteActionInvoker::class));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
