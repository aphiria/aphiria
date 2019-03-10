<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Compilers\Tokenizers;

use Aphiria\Console\Input\Compilers\Tokenizers\ArrayListInputTokenizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the array list input tokenizer
 */
class ArrayListInputTokenizerTest extends TestCase
{
    /** @var ArrayListInputTokenizer */
    private $tokenizer;

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
