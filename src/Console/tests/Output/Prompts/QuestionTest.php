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

use Aphiria\Console\Output\Prompts\Question;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    private Question $question;

    protected function setUp(): void
    {
        $this->question = new Question('Dummy question', 'foo');
    }

    public function testFormattingAnswer(): void
    {
        $this->assertSame('foo', $this->question->formatAnswer('foo'));
    }

    public function testGettingDefaultAnswer(): void
    {
        $this->assertSame('foo', $this->question->defaultAnswer);
    }

    public function testGettingQuestion(): void
    {
        $this->assertSame('Dummy question', $this->question->text);
    }
}
