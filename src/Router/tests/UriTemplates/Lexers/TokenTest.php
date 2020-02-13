<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Lexers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenTypes;
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
