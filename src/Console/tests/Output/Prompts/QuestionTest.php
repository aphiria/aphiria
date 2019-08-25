<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Prompts;

use Aphiria\Console\Output\Prompts\Question;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console prompt question
 */
class QuestionTest extends TestCase
{
    private Question $question;

    protected function setUp(): void
    {
        $this->question = new Question('Dummy question', 'foo');
    }

    public function testFormattingAnswer(): void
    {
        $this->assertEquals('foo', $this->question->formatAnswer('foo'));
    }

    public function testGettingDefaultAnswer(): void
    {
        $this->assertEquals('foo', $this->question->defaultAnswer);
    }

    public function testGettingQuestion(): void
    {
        $this->assertEquals('Dummy question', $this->question->text);
    }
}
