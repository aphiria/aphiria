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

use Aphiria\Api\Controllers\FailedRequestParameterConversionException;
use Aphiria\Api\Controllers\IRequestParameterDeserializer;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Controllers\RequestParameterDeserializer;
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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
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
        $this->container
            ->shouldReceive('resolve')
            ->with(IValidator::class)
            ->andReturn($this->createMock(IValidator::class));
        $this->container
            ->shouldReceive('resolve')
            ->with(IErrorMessageInterpolator::class)
            ->andReturn($this->createMock(IErrorMessageInterpolator::class));
        $this->container
            ->shouldReceive('resolve')
            ->with(IContentNegotiator::class)
            ->andReturn($this->createMock(IContentNegotiator::class));
        $this->container
            ->shouldReceive('resolve')
            ->with(IBodyDeserializer::class)
            ->andReturn($this->createMock(IBodyDeserializer::class));
        $this->container
            ->shouldReceive('resolve')
            ->with(IResponseFactory::class)
            ->andReturn($this->createMock(IResponseFactory::class));

        // Set up the date format config
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration([
            'aphiria' => [
                'serialization' => [
                    'dateFormat' => 'Y-m-d',
                    'dateTimeFormat' => DateTimeInterface::ATOM,
                ],
            ],
        ]));
    }

    public function testDateTimeCanBeDeserializedUsingDateFormat(): void
    {
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, Mockery::on(function (mixed $value): bool {
                $this->assertInstanceOf(RequestParameterDeserializer::class, $value);
                /** @var RequestParameterDeserializer $value */
                // Zero out the time so we don't get weirdness with microseconds
                $now = new DateTime()->setTime(0, 0);
                /** @var DateTime $actualDateTime */
                $actualDateTime = $value->deserializeRouteActionParameter(DateTime::class, $now->format('Y-m-d'));
                $this->assertEquals($now, $actualDateTime->setTime(0, 0));

                return true;
            }));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testDateTimeCanBeDeserializedUsingDateTimeFormat(): void
    {
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, Mockery::on(function (mixed $value): bool {
                $this->assertInstanceOf(RequestParameterDeserializer::class, $value);
                /** @var RequestParameterDeserializer $value */
                // Zero out the time so we don't get weirdness with microseconds
                $now = new DateTime()->setTime(0, 0);
                $this->assertEquals($now, $value->deserializeRouteActionParameter(DateTime::class, $now->format(DateTimeInterface::ATOM)));

                return true;
            }));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testDateTimeImmutableCanBeDeserializedUsingDateFormat(): void
    {
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, Mockery::on(function (mixed $value): bool {
                $this->assertInstanceOf(RequestParameterDeserializer::class, $value);
                /** @var RequestParameterDeserializer $value */
                // Zero out the time so we don't get weirdness with microseconds
                $now = new DateTimeImmutable()->setTime(0, 0);
                /** @var DateTimeImmutable $actualDateTime */
                $actualDateTime = $value->deserializeRouteActionParameter(DateTimeImmutable::class, $now->format('Y-m-d'));
                $this->assertEquals($now, $actualDateTime->setTime(0, 0));

                return true;
            }));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testDateTimeImmutableCanBeDeserializedUsingDateTimeFormat(): void
    {
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, Mockery::on(function (mixed $value): bool {
                $this->assertInstanceOf(RequestParameterDeserializer::class, $value);
                /** @var RequestParameterDeserializer $value */
                // Zero out the time so we don't get weirdness with microseconds
                $now = new DateTimeImmutable()->setTime(0, 0);
                $this->assertEquals($now, $value->deserializeRouteActionParameter(DateTimeImmutable::class, $now->format(DateTimeInterface::ATOM)));

                return true;
            }));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testDateTimeImmutableThatCannotBeDeserializedThrowsException(): void
    {
        $this->expectException(FailedRequestParameterConversionException::class);
        $this->expectExceptionMessage('Could not convert "foo" to ' . DateTimeImmutable::class);
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, Mockery::on(function (mixed $value): bool {
                $this->assertInstanceOf(RequestParameterDeserializer::class, $value);
                /** @var RequestParameterDeserializer $value */
                $value->deserializeRouteActionParameter(DateTimeImmutable::class, 'foo');

                return true;
            }));
        $this->binder->bind($this->container);
    }

    public function testDateTimeThatCannotBeDeserializedThrowsException(): void
    {
        $this->expectException(FailedRequestParameterConversionException::class);
        $this->expectExceptionMessage('Could not convert "foo" to ' . DateTime::class);
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRequestParameterDeserializer::class, Mockery::on(function (mixed $value): bool {
                $this->assertInstanceOf(RequestParameterDeserializer::class, $value);
                /** @var RequestParameterDeserializer $value */
                $value->deserializeRouteActionParameter(DateTime::class, 'foo');

                return true;
            }));
        $this->binder->bind($this->container);
    }

    public function testRouteActionInvokerIsBound(): void
    {
        $this->container
            ->shouldReceive('bindInstance')
            ->with(IRouteActionInvoker::class, $this->isInstanceOf(RouteActionInvoker::class));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
