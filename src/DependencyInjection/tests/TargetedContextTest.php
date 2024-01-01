<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\TargetedContext;
use PHPUnit\Framework\TestCase;

class TargetedContextTest extends TestCase
{
    public function testMethodsIndicateATargetedContext(): void
    {
        $context = new TargetedContext(self::class);
        $this->assertSame(self::class, $context->targetClass);
        $this->assertTrue($context->isTargeted);
        $this->assertFalse($context->isUniversal);
    }
}
