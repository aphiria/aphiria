<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class UniversalContextTest extends TestCase
{
    public function testMethodsIndicateUniversalContext(): void
    {
        $context = new UniversalContext();
        $this->assertNull($context->targetClass);
        $this->assertFalse($context->isTargeted);
        $this->assertTrue($context->isUniversal);
    }
}
