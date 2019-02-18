<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers\Nodes;

use Aphiria\Console\Output\Compilers\Parsers\Nodes\RootNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the root node
 */
class RootNodeTest extends TestCase
{
    public function testIsRoot(): void
    {
        $node = new RootNode();
        $this->assertTrue($node->isRoot());
    }

    public function testIsTag(): void
    {
        $node = new RootNode();
        $this->assertFalse($node->isTag());
    }

    public function testParentIsNull(): void
    {
        $node = new RootNode();
        $this->assertNull($node->parent);
    }
}
