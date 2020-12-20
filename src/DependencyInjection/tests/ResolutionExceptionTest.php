<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
        $expectedContext = new TargetedContext(self::class);
        $exception = new ResolutionException(self::class, $expectedContext);
        $this->assertSame($expectedContext, $exception->getContext());
    }

    public function testGetInterfaceReturnsInterfaceInjectedInConstructor(): void
    {
        $exception = new ResolutionException(self::class, new UniversalContext());
        $this->assertSame(self::class, $exception->getInterface());
    }
}
