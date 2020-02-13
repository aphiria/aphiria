<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers;

use Aphiria\Console\Output\Parsers\TagAstNode;
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
