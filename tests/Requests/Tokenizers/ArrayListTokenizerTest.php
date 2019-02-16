<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Tokenizers;

use Aphiria\Console\Requests\Tokenizers\ArrayListTokenizer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the array list tokenizer
 */
class ArrayListTokenizerTest extends TestCase
{
    /** @var ArrayListTokenizer The tokenizer to use in tests */
    private $tokenizer;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->tokenizer = new ArrayListTokenizer();
    }

    /**
     * Test not passing the command name
     */
    public function testNotPassingCommandName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->tokenizer->tokenize([
            'foo' => 'bar'
        ]);
    }

    /**
     * Tests tokenizing arguments and options
     */
    public function testTokenizingArgumentsAndOptions(): void
    {
        $tokens = $this->tokenizer->tokenize([
            'name' => 'foo',
            'arguments' => ['bar'],
            'options' => ['--name=dave', '-r']
        ]);
        $this->assertEquals(['foo', 'bar', '--name=dave', '-r'], $tokens);
    }
}
