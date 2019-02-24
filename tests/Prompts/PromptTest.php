<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Prompts;

use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\Prompts\Prompt;
use Aphiria\Console\Prompts\Questions\MultipleChoice;
use Aphiria\Console\Prompts\Questions\Question;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console prompt
 */
class PromptTest extends TestCase
{
    /** @var IOutput|MockObject */
    private $output;
    /** @var PaddingFormatter */
    private $paddingFormatter;
    /** @var Prompt */
    private $prompt;

    public function setUp(): void
    {
        /** @var IOutput|MockObject output */
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
        $this->assertEquals('Dave', $answer);
    }

    public function testAskingIndexedMultipleChoiceQuestion(): void
    {
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $this->output->method('readLine')
            ->willReturn('2');
        $this->output->expects($this->at(0))
            ->method('write')
            ->with("<question>{$question->text}</question>");
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('');
        $this->output->expects($this->at(2))
            ->method('writeln')
            ->with('  1) foo' . PHP_EOL . '  2) bar');
        $this->output->expects($this->at(3))
            ->method('write')
            ->with('  > ');
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertEquals('bar', $answer);
    }

    public function testAskingKeyedMultipleChoiceQuestion(): void
    {
        $question = new MultipleChoice('Pick', ['a' => 'b', 'c' => 'd']);
        $this->output->method('readLine')
            ->willReturn('c');
        $this->output->expects($this->at(0))
            ->method('write')
            ->with("<question>{$question->text}</question>");
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('');
        $this->output->expects($this->at(2))
            ->method('writeln')
            ->with('  a) b' . PHP_EOL . '  c) d');
        $this->output->expects($this->at(3))
            ->method('write')
            ->with('  > ');
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertEquals('d', $answer);
    }

    public function testAskingMultipleChoiceQuestionWithCustomAnswerLineString(): void
    {
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $question->setAnswerLineString('  : ');
        $this->output->method('readLine')
            ->willReturn('1');
        $this->output->expects($this->at(0))
            ->method('write')
            ->with("<question>{$question->text}</question>");
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('');
        $this->output->expects($this->at(2))
            ->method('writeln')
            ->with('  1) foo' . PHP_EOL . '  2) bar');
        $this->output->expects($this->at(3))
            ->method('write')
            ->with('  : ');
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertEquals('foo', $answer);
    }

    public function testAskingQuestion(): void
    {
        $question = new Question('Name of dev', 'unknown');
        $this->output->method('readLine')
            ->willReturn('Dave');
        $this->output->method('write')
            ->with("<question>{$question->text}</question>");
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertEquals('Dave', $answer);
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
        $this->assertEquals('unknown', $answer);
    }
}
