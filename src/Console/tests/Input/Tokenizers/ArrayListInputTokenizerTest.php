<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Tokenizers;

use Aphiria\Console\Input\Tokenizers\ArrayListInputTokenizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ArrayListInputTokenizerTest extends TestCase
{
    private ArrayListInputTokenizer $tokenizer;

    protected function setUp(): void
    {
        $this->tokenizer = new ArrayListInputTokenizer();
    }

    public function testNotPassingCommandName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->tokenizer->tokenize([
            'foo' => 'bar'
        ]);
    }

    public function testTokenizingArgumentsAndOptions(): void
    {
        $tokens = $this->tokenizer->tokenize([
            'name' => 'foo',
            'arguments' => ['bar'],
            'options' => ['--name=dave', '-r']
        ]);
        $this->assertEquals(['foo', 'bar', '--name=dave', '-r'], $tokens);
    }

    public function testTokenizingNonArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->tokenizer->tokenize('foo');
    }
}
