<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\WordAstNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests the word node
 */
class WordNodeTest extends TestCase
{
    public function testIsTag(): void
    {
        $node = new WordAstNode('foo');
        $this->assertFalse($node->isTag());
    }
}
