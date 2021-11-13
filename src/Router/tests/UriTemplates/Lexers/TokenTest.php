<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Lexers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenType;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $token = new Token(TokenType::Text, 'foo');
        $this->assertSame(TokenType::Text, $token->type);
        $this->assertSame('foo', $token->value);
    }
}
