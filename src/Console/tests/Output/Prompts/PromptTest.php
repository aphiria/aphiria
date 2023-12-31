<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class PromptTest extends TestCase
{
    private IOutput|MockInterface $output;
    private PaddingFormatter $paddingFormatter;
    private Prompt $prompt;

    protected function setUp(): void
    {
        $this->output = Mockery::mock(IOutput::class);
        $this->paddingFormatter = new PaddingFormatter();
        $this->prompt = new Prompt($this->paddingFormatter);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAnsweringWithSpacesWillTrimThem(): void
    {
        $question = new Question('Name of dev', 'unknown');
        $this->output->shouldReceive('readline')
            ->andReturn('   Dave   ');
        $this->output->shouldReceive('write')
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
        $this->output->shouldReceive('write')
            ->with('<question>Question</question>');
        $this->output->shouldReceive('getDriver')
            ->times(1)
            ->andReturn($driver);
        $answer = $this->prompt->ask(new Question('Question', null, true), $this->output);
        $this->assertSame('foo', $answer);
    }

    public function testAskingIndexedMultipleChoiceQuestion(): void
    {
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $this->output->shouldReceive('readLine')
            ->andReturn('2');
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $this->output->shouldReceive('write')
            ->with('  > ');
        $this->output->shouldReceive('writeln')
            ->with('');
        $this->output->shouldReceive('writeln')
            ->with('  1) foo' . PHP_EOL . '  2) bar');
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('bar', $answer);
    }

    public function testAskingKeyedMultipleChoiceQuestion(): void
    {
        $question = new MultipleChoice('Pick', ['a' => 'b', 'c' => 'd']);
        $this->output->shouldReceive('readLine')
            ->andReturn('c');
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $this->output->shouldReceive('write')
            ->with('  > ');
        $this->output->shouldReceive('writeln')
            ->with('');
        $this->output->shouldReceive('writeln')
            ->with('  a) b' . PHP_EOL . '  c) d');
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('d', $answer);
    }

    public function testAskingMultipleChoiceQuestionWithCustomAnswerLineString(): void
    {
        $question = new MultipleChoice('Pick', ['foo', 'bar']);
        $question->answerLineString = '  : ';
        $this->output->shouldReceive('readLine')
            ->andReturn('1');
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $this->output->shouldReceive('write')
            ->with('  : ');
        $this->output->shouldReceive('writeln')
            ->with('');
        $this->output->shouldReceive('writeln')
            ->with('  1) foo' . PHP_EOL . '  2) bar');
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('foo', $answer);
    }

    public function testAskingQuestion(): void
    {
        $question = new Question('Name of dev', 'unknown');
        $this->output->shouldReceive('readLine')
            ->andReturn('Dave');
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('Dave', $answer);
    }

    public function testEmptyDefaultAnswerToIndexedChoices(): void
    {
        $triggeredException = false;
        $question = new MultipleChoice('Dummy question', ['foo', 'bar']);
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $this->output->shouldReceive('writeln')
            ->with('');
        $this->output->shouldReceive('writeln')
            ->with('  1) foo' . PHP_EOL . '  2) bar');
        $this->output->shouldReceive('writeln')
            ->with($question->answerLineString);
        $this->output->shouldReceive('write')
            ->with('  > ');
        $this->output->shouldReceive('readLine')
            ->andReturn(' ');

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
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $this->output->shouldReceive('writeln')
            ->with('');
        $this->output->shouldReceive('writeln')
            ->with('  foo) bar ' . PHP_EOL . '  baz) blah');
        $this->output->shouldReceive('writeln')
            ->with($question->answerLineString);
        $this->output->shouldReceive('write')
            ->with('  > ');
        $this->output->shouldReceive('readLine')
            ->andReturn(' ');

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
        $this->output->shouldReceive('readLine')
            ->andReturn(' ');
        $this->output->shouldReceive('write')
            ->with("<question>{$question->text}</question>");
        $answer = $this->prompt->ask($question, $this->output);
        $this->assertSame('unknown', $answer);
    }
}
