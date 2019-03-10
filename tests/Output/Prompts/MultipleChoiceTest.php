<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Prompts;

use Aphiria\Console\Output\Prompts\MultipleChoice;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the multiple choice question
 */
class MultipleChoiceTest extends TestCase
{
    /** @var MultipleChoice */
    private $indexedChoiceQuestion;
    /** @var MultipleChoice */
    private $keyedChoiceQuestion;

    protected function setUp(): void
    {
        $this->indexedChoiceQuestion = new MultipleChoice('Dummy question', ['foo', 'bar', 'baz']);
        $this->keyedChoiceQuestion = new MultipleChoice('Dummy question', ['a' => 'b', 'c' => 'd', 'e' => 'f']);
    }

    public function testAnswerOutOfBounds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->indexedChoiceQuestion->formatAnswer(4);
    }

    public function testCheckingIfChoicesAreAssociative(): void
    {
        $this->assertFalse($this->indexedChoiceQuestion->choicesAreAssociative());
        $this->assertTrue($this->keyedChoiceQuestion->choicesAreAssociative());
    }

    public function testEmptyAnswerForAssociativeChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->keyedChoiceQuestion->formatAnswer('');
    }

    public function testEmptyAnswerForIndexedChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->indexedChoiceQuestion->formatAnswer('');
    }

    public function testFloatAsAnswerToIndexedChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->indexedChoiceQuestion->formatAnswer(1.5);
    }

    public function testFormattingMultipleAnswers(): void
    {
        $this->indexedChoiceQuestion->setAllowsMultipleChoices(true);
        $this->keyedChoiceQuestion->setAllowsMultipleChoices(true);
        $this->assertEquals(['foo', 'bar'], $this->indexedChoiceQuestion->formatAnswer('1,2'));
        $this->assertEquals(['d', 'f'], $this->keyedChoiceQuestion->formatAnswer('c,e'));
    }

    public function testFormattingMultipleAnswersWithSpaces(): void
    {
        $this->indexedChoiceQuestion->setAllowsMultipleChoices(true);
        $this->keyedChoiceQuestion->setAllowsMultipleChoices(true);
        $this->assertEquals(['bar', 'baz'], $this->indexedChoiceQuestion->formatAnswer('2, 3'));
        $this->assertEquals(['b', 'f'], $this->keyedChoiceQuestion->formatAnswer('a, e'));
    }

    public function testFormattingSingleAnswer(): void
    {
        $this->assertEquals('foo', $this->indexedChoiceQuestion->formatAnswer(1));
        $this->assertEquals('bar', $this->indexedChoiceQuestion->formatAnswer(2));
        $this->assertEquals('baz', $this->indexedChoiceQuestion->formatAnswer(3));
    }

    public function testFormattingStringAnswer(): void
    {
        $this->assertEquals('foo', $this->indexedChoiceQuestion->formatAnswer('1'));
        $this->assertEquals('bar', $this->indexedChoiceQuestion->formatAnswer('2'));
        $this->assertEquals('baz', $this->indexedChoiceQuestion->formatAnswer('3'));
        $this->assertEquals('b', $this->keyedChoiceQuestion->formatAnswer('a'));
        $this->assertEquals('d', $this->keyedChoiceQuestion->formatAnswer('c'));
        $this->assertEquals('f', $this->keyedChoiceQuestion->formatAnswer('e'));
    }

    public function testGettingAllowsMultipleChoices(): void
    {
        $this->assertFalse($this->indexedChoiceQuestion->allowsMultipleChoices());
    }

    public function testGettingAnswerLineString(): void
    {
        $this->indexedChoiceQuestion->setAnswerLineString(' > ');
        // Essentially just test that we got here
        $this->assertTrue(true);
    }

    public function testGettingChoices(): void
    {
        $this->assertEquals(['foo', 'bar', 'baz'], $this->indexedChoiceQuestion->choices);
        $this->assertEquals(['a' => 'b', 'c' => 'd', 'e' => 'f'], $this->keyedChoiceQuestion->choices);
    }

    public function testInvalidAnswerForKeyedChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->keyedChoiceQuestion->formatAnswer('p');
    }

    public function testMultipleIndexedChoicesWhenNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->indexedChoiceQuestion->setAllowsMultipleChoices(false);
        $this->indexedChoiceQuestion->formatAnswer('1,2');
    }

    public function testMultipleKeyedChoicesWhenNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->keyedChoiceQuestion->setAllowsMultipleChoices(false);
        $this->keyedChoiceQuestion->formatAnswer('a,c');
    }

    public function testNullAnswerToIndexedChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->indexedChoiceQuestion->formatAnswer(null);
    }

    public function testNullAnswerToKeyedChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->keyedChoiceQuestion->formatAnswer(null);
    }

    public function testSettingAllowsMultipleChoices(): void
    {
        $this->indexedChoiceQuestion->setAllowsMultipleChoices(true);
        $this->assertTrue($this->indexedChoiceQuestion->allowsMultipleChoices());
    }

    public function testSettingAnswerLineString(): void
    {
        $this->indexedChoiceQuestion->setAnswerLineString('foo');
        $this->assertEquals('foo', $this->indexedChoiceQuestion->getAnswerLineString());
    }

    public function testStringAsAnswerToIndexedChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->indexedChoiceQuestion->formatAnswer('foo');
    }
}
