<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Parsers\Lexers;

use Opulence\Routing\UriTemplates\Parsers\Lexers\Token;
use Opulence\Routing\UriTemplates\Parsers\Lexers\TokenTypes;
use PHPUnit\Framework\TestCase;

/**
 * Tests a lexer token
 */
class TokenTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $token = new Token(TokenTypes::T_TEXT, 'foo');
        $this->assertEquals(TokenTypes::T_TEXT, $token->type);
        $this->assertEquals('foo', $token->value);
    }
}
