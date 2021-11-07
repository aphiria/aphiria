<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Prompts;

use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\Output\Prompts\MultipleChoice;
use Aphiria\Console\Output\Prompts\Prompt;
use Aphiria\Console\Output\Prompts\Question;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PromptTest extends TestCase
{
    private IOutput&MockObject $output;
    private PaddingFormatter $paddingFormatter;
    private Prompt $prompt;

    protected function setUp(): void
    {
        /** @var IOutput&MockObject output */
        $this->output = $this->createMock(IOutput::class);
        $this->paddingFormatter = new PaddingFormatter();
        $this->prompt = new Prompt($this->paddingFormatter);
    }

    public function testAnsweringWithSpacesWillTrimThem(): void
    {
        $question = new Question('Name of dev', 'unknown');
        $this->output->method('readLine')
            ->willReturn('  Dave  ');
        $this->output->method('write')
            ->with("<question>{$question->text}</question>");
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('Dave', $answer);
    }

    public function testAskingIndexedMultipleChoiceQuestion(): void
    {
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $this->output->method('readLine')
            ->willReturn('2');
        $this->output->method('write')
            ->withConsecutive(["<question>{$question->text}</question>"], ['  > ']);
        $this->output->method('writeln')
            ->withConsecutive([''], ['  1) foo' . PHP_EOL . '  2) bar']);
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('bar', $answer);
    }

    public function testAskingKeyedMultipleChoiceQuestion(): void
    {
        $question = new MultipleChoice('Pick', ['a' => 'b', 'c' => 'd']);
        $this->output->method('readLine')
            ->willReturn('c');
        $this->output->method('write')
            ->withConsecutive(["<question>{$question->text}</question>"], ['  > ']);
        $this->output->method('writeln')
            ->withConsecutive([''], ['  a) b' . PHP_EOL . '  c) d']);
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('d', $answer);
    }

    public function testAskingMultipleChoiceQuestionWithCustomAnswerLineString(): void
    {
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $question->answerLineString = '  : ';
        $this->output->method('readLine')
            ->willReturn('1');
        $this->output->method('write')
            ->withConsecutive(["<question>{$question->text}</question>"], ['  : ']);
        $this->output->method('writeln')
            ->withConsecutive([''], ['  1) foo' . PHP_EOL . '  2) bar']);
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('foo', $answer);
    }

    public function testAskingQuestion(): void
    {
        $question = new Question('Name of dev', 'unknown');
        $this->output->method('readLine')
            ->willReturn('Dave');
        $this->output->method('write')
            ->with("<question>{$question->text}</question>");
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('Dave', $answer);
    }

    public function testAskingHiddenAnswerQuestionWillUseDriver(): void
    {
        $driver = $this->createMock(IDriver::class);
        $driver->expects($this->once())
            ->method('readHiddenInput')
            ->with($this->output)
            ->willReturn('foo');
        $this->output->expects($this->once())
            ->method('getDriver')
            ->willReturn($driver);
        $answer = $this->prompt->ask(new Question('Question', null, true), $this->output);
        $this->assertSame('foo', $answer);
    }

    public function testEmptyDefaultAnswerToIndexedChoices(): void
    {
        $triggeredException = false;
        $question = new MultipleChoice('Dummy question', ['foo', 'bar']);
        $this->output->method('readLine')
            ->willReturn(' ');

        try {
            $this->prompt->ask($question, $this->output);
        } catch (InvalidArgumentException $ex) {
            $triggeredException = true;
        }

        $this->assertTrue($triggeredException);
    }

    public function testEmptyDefaultAnswerToKeyedChoices(): void
    {
        $triggeredException = false;
        $question = new MultipleChoice('Dummy question', ['foo' => 'bar', 'baz' => 'blah']);
        $this->output->method('readLine')
            ->willReturn(' ');

        try {
            $this->prompt->ask($question, $this->output);
        } catch (InvalidArgumentException $ex) {
            $triggeredException = true;
        }

        $this->assertTrue($triggeredException);
    }

    public function testNotReceivingAnswerUsesDefaultAnswer(): void
    {
        $question = new Question('Name of dev', 'unknown');
        $this->output->method('readLine')
            ->willReturn(' ');
        $this->output->method('write')
            ->with("<question>{$question->text}</question>");
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('unknown', $answer);
    }
}
