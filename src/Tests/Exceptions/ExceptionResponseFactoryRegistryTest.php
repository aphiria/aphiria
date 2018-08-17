<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use InvalidArgumentException;
use Opulence\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\RequestContext;

/**
 * Tests the exception response factory registry
 */
class ExceptionResponseFactoryRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExceptionResponseFactoryRegistry The registry to use in tests */
    private $registry;

    public function setUp(): void
    {
        $this->registry = new ExceptionResponseFactoryRegistry();
    }

    public function testGettingFactoryForExceptionTypeThatDoesNotHaveFactoryReturnsNull(): void
    {
        $this->assertNull($this->registry->getFactory(InvalidArgumentException::class));
    }

    public function testGettingFactoryForExceptionTypeThatHasFactoryReturnsTheFactory(): void
    {
        $expectedFactory = function (HttpException $ex, RequestContext $requestContext) {
            // Don't do anything
        };
        $this->registry->registerFactory(InvalidArgumentException::class, $expectedFactory);
        $this->assertSame($expectedFactory, $this->registry->getFactory(InvalidArgumentException::class));
    }
}
