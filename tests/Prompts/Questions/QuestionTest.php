<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Prompts\Questions;

use Aphiria\Console\Prompts\Questions\Question;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console prompt question
 */
class QuestionTest extends TestCase
{
    /** @var Question The question to use in tests */
    private $question;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->question = new Question('Dummy question', 'foo');
    }

    /**
     * Tests formatting the answer
     */
    public function testFormattingAnswer(): void
    {
        $this->assertEquals('foo', $this->question->formatAnswer('foo'));
    }

    /**
     * Tests getting the default response
     */
    public function testGettingDefaultResponse(): void
    {
        $this->assertEquals('foo', $this->question->getDefaultAnswer());
    }

    /**
     * Tests getting the question
     */
    public function testGettingQuestion(): void
    {
        $this->assertEquals('Dummy question', $this->question->getText());
    }
}
