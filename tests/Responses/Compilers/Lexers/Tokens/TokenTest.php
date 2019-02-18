<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers\Lexers\Tokens;

use Aphiria\Console\Responses\Compilers\Lexers\Tokens\Token;
use Aphiria\Console\Responses\Compilers\Lexers\Tokens\TokenTypes;
use PHPUnit\Framework\TestCase;

/**
 * Tests the response token
 */
class TokenTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $token = new Token(TokenTypes::T_WORD, 'foo', 24);
        $this->assertEquals(TokenTypes::T_WORD, $token->type);
        $this->assertEquals('foo', $token->value);
        $this->assertEquals(24, $token->position);
    }
}
