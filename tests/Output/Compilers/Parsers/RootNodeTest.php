<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\RootAstNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the root node
 */
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
