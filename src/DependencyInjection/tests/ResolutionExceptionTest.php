<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class ResolutionExceptionTest extends TestCase
{
    public function testGetContextReturnsContextInjectedInConstructor(): void
    {
        $expectedContext = new TargetedContext('bar');
        $exception = new ResolutionException('foo', $expectedContext);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    public function testGetInterfaceReturnsInterfaceInjectedInConstructor(): void
    {
        $exception = new ResolutionException('foo', new UniversalContext());
        $this->assertEquals('foo', $exception->getInterface());
    }
}
