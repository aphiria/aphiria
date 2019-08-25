<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers\Parsers\Lexers;

use Aphiria\Console\Output\Compilers\Parsers\Lexers\OutputToken;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\OutputTokenTypes;
use PHPUnit\Framework\TestCase;

/**
 * Tests the output token
 */
class OutputTokenTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $token = new OutputToken(OutputTokenTypes::T_WORD, 'foo', 24);
        $this->assertEquals(OutputTokenTypes::T_WORD, $token->type);
        $this->assertEquals('foo', $token->value);
        $this->assertEquals(24, $token->position);
    }
}
