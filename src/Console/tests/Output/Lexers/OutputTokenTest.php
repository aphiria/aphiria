<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Lexers;

use Aphiria\Console\Output\Lexers\OutputToken;
use Aphiria\Console\Output\Lexers\OutputTokenType;
use PHPUnit\Framework\TestCase;

class OutputTokenTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $token = new OutputToken(OutputTokenType::Word, 'foo', 24);
        $this->assertSame(OutputTokenType::Word, $token->type);
        $this->assertSame('foo', $token->value);
        $this->assertSame(24, $token->position);
    }
}
