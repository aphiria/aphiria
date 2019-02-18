<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses;

use Aphiria\Console\Responses\Compilers\ResponseCompiler;
use Aphiria\Console\Responses\Compilers\Lexers\Lexer;
use Aphiria\Console\Responses\Compilers\Parsers\Parser;
use Aphiria\Console\Tests\Responses\Mocks\Response;
use PHPUnit\Framework\TestCase;

/**
 * Tests the response class
 */
class ResponseTest extends TestCase
{
    /** @var Response The response to use in tests */
    private $response;

    public function setUp(): void
    {
        $this->response = new Response(new ResponseCompiler(new Lexer(), new Parser()));
    }

    public function testClearingResponse(): void
    {
        ob_start();
        $this->response->clear();
        $this->assertEquals(chr(27) . '[2J' . chr(27) . '[;H', ob_get_clean());
    }

    public function testWritingMultipleMessagesWithNewLines(): void
    {
        ob_start();
        $this->response->writeln(['foo', 'bar']);
        $this->assertEquals('foo' . PHP_EOL . 'bar' . PHP_EOL, ob_get_clean());
    }

    public function testWritingMultipleMessagesWithNoNewLines(): void
    {
        ob_start();
        $this->response->write(['foo', 'bar']);
        $this->assertEquals('foobar', ob_get_clean());
    }

    public function testWritingSingleMessageWithNewLine(): void
    {
        ob_start();
        $this->response->writeln('foo');
        $this->assertEquals('foo' . PHP_EOL, ob_get_clean());
    }

    public function testWritingSingleMessageWithNoNewLine(): void
    {
        ob_start();
        $this->response->write('foo');
        $this->assertEquals('foo', ob_get_clean());
    }

    public function testWritingStyledMessageWithStylingDisabled(): void
    {
        ob_start();
        $this->response->setStyled(false);
        $this->response->write('<b>foo</b>');
        $this->assertEquals('foo', ob_get_clean());
    }
}
