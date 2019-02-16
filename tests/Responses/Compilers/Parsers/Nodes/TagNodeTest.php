<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers\Parsers\Nodes;

use Aphiria\Console\Responses\Compilers\Parsers\Nodes\TagNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the tag node
 */
class TagNodeTest extends TestCase
{
    /**
     * Tests checking if a root node is a tag
     */
    public function testIsTag(): void
    {
        $node = new TagNode('foo');
        $this->assertTrue($node->isTag());
    }
}
