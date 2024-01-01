<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers;

use Aphiria\Console\Output\Parsers\RootAstNode;
use PHPUnit\Framework\TestCase;

class RootNodeTest extends TestCase
{
    public function testIsRoot(): void
    {
        $node = new RootAstNode();
        $this->assertTrue($node->isRoot());
    }

    public function testIsTag(): void
    {
        $node = new RootAstNode();
        $this->assertFalse($node->isTag());
    }

    public function testParentIsNull(): void
    {
        $node = new RootAstNode();
        $this->assertNull($node->parent);
    }
}
