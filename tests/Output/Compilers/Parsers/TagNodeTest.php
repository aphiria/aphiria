<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\TagAstNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the tag node
 */
class TagNodeTest extends TestCase
{
    public function testIsTag(): void
    {
        $node = new TagAstNode('foo');
        $this->assertTrue($node->isTag());
    }
}
