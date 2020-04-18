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

use Aphiria\DependencyInjection\TargetedContext;
use PHPUnit\Framework\TestCase;

class TargetedContextTest extends TestCase
{
    public function testMethodsIndicateATargetedContext(): void
    {
        $context = new TargetedContext('foo');
        $this->assertEquals('foo', $context->getTargetClass());
        $this->assertTrue($context->isTargeted());
        $this->assertFalse($context->isUniversal());
    }
}
