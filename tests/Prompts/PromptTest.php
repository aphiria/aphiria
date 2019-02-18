<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Prompts;

use Aphiria\Console\Prompts\Prompt;
use Aphiria\Console\Prompts\Questions\MultipleChoice;
use Aphiria\Console\Prompts\Questions\Question;
use Aphiria\Console\Responses\Compilers\Lexers\ResponseLexer;
use Aphiria\Console\Responses\Compilers\Parsers\ResponseParser;
use Aphiria\Console\Responses\Compilers\ResponseCompiler;
use Aphiria\Console\Responses\Formatters\PaddingFormatter;
use Aphiria\Console\Tests\Responses\Mocks\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console prompt
 */
class PromptTest extends TestCase
{
    /** @var Response The response to use in tests */
    private $response;
    /** @var PaddingFormatter The space padding formatter to use in tests */
    private $paddingFormatter;

    public function setUp(): void
    {
        $this->response = new Response(new ResponseCompiler(new ResponseLexer(), new ResponseParser()));
        $this->paddingFormatter = new PaddingFormatter();
    }

    public function testAnsweringWithSpaces(): void
    {
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream('  Dave  '));
        $question = new Question('Name of dev', 'unknown');
        ob_start();
        $answer = $prompt->ask($question, $this->response);
        $questionText = ob_get_clean();
        $this->assertEquals("\033[37;44m{$question->text}\033[39;49m", $questionText);
        $this->assertEquals('Dave', $answer);
    }

    public function testAskingIndexedMultipleChoiceQuestion(): void
    {
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream('2'));
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        ob_start();
        $answer = $prompt->ask($question, $this->response);
        $questionText = ob_get_clean();
        $this->assertEquals(
            "\033[37;44m{$question->text}\033[39;49m" . PHP_EOL . '  1) foo' . PHP_EOL . '  2) bar' . PHP_EOL . '  > ',
            $questionText
        );
        $this->assertEquals('bar', $answer);
    }

    public function testAskingKeyedMultipleChoiceQuestion(): void
    {
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream('c'));
        $question = new MultipleChoice('Pick', ['a' => 'b', 'c' => 'd']);
        ob_start();
        $answer = $prompt->ask($question, $this->response);
        $questionText = ob_get_clean();
        $this->assertEquals(
            "\033[37;44m{$question->text}\033[39;49m" . PHP_EOL . '  a) b' . PHP_EOL . '  c) d' . PHP_EOL . '  > ',
            $questionText
        );
        $this->assertEquals('d', $answer);
    }

    public function testAskingMultipleChoiceQuestionWithCustomAnswerLineString(): void
    {
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream('1'));
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $question->setAnswerLineString('  : ');
        ob_start();
        $answer = $prompt->ask($question, $this->response);
        $questionText = ob_get_clean();
        $this->assertEquals(
            "\033[37;44m{$question->text}\033[39;49m" . PHP_EOL . '  1) foo' . PHP_EOL . '  2) bar' . PHP_EOL . '  : ',
            $questionText
        );
        $this->assertEquals('foo', $answer);
    }

    public function testAskingQuestion(): void
    {
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream('Dave'));
        $question = new Question('Name of dev', 'unknown');
        ob_start();
        $answer = $prompt->ask($question, $this->response);
        $questionText = ob_get_clean();
        $this->assertEquals("\033[37;44m{$question->text}\033[39;49m", $questionText);
        $this->assertEquals('Dave', $answer);
    }

    public function testEmptyDefaultAnswerToIndexedChoices(): void
    {
        $triggeredException = false;
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream(' '));
        $question = new MultipleChoice('Dummy question', ['foo', 'bar']);
        ob_start();

        try {
            $prompt->ask($question, $this->response);
        } catch (InvalidArgumentException $ex) {
            $triggeredException = true;
            ob_end_clean();
        }

        $this->assertTrue($triggeredException);
    }

    public function testEmptyDefaultAnswerToKeyedChoices(): void
    {
        $triggeredException = false;
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream(' '));
        $question = new MultipleChoice('Dummy question', ['foo' => 'bar', 'baz' => 'blah']);
        ob_start();

        try {
            $prompt->ask($question, $this->response);
        } catch (InvalidArgumentException $ex) {
            $triggeredException = true;
            ob_end_clean();
        }

        $this->assertTrue($triggeredException);
    }

    public function testNotReceivingResponse(): void
    {
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream(' '));
        $question = new Question('Name of dev', 'unknown');
        ob_start();
        $answer = $prompt->ask($question, $this->response);
        $questionText = ob_get_clean();
        $this->assertEquals("\033[37;44m{$question->text}\033[39;49m", $questionText);
        $this->assertEquals('unknown', $answer);
    }

    public function testSettingInvalidInputStreamThroughConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Prompt($this->paddingFormatter, 'foo');
    }

    public function testSettingInvalidInputStreamThroughSetter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $prompt = new Prompt($this->paddingFormatter, $this->getInputStream('foo'));
        $prompt->setInputStream('foo');
    }

    /**
     * Gets an input stream for use in tests
     *
     * @param mixed $input The input to write to the stream
     * @return resource The input stream to use in tests
     */
    private function getInputStream($input)
    {
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }
}
