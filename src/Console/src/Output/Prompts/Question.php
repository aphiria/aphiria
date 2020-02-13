<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Prompts;

use InvalidArgumentException;

/**
 * Defines a console prompt question
 */
class Question
{
    /** @var string The question text */
    public string $text;
    /** @var mixed The default answer to the question */
    public $defaultAnswer;

    /**
     * @param string $text The question text
     * @param mixed $defaultAnswer The default answer to the question
     */
    public function __construct(string $text, $defaultAnswer = null)
    {
        $this->text = $text;
        $this->defaultAnswer = $defaultAnswer;
    }

    /**
     * Formats an answer
     * Useful for subclasses to override
     *
     * @param mixed $answer The answer to format
     * @return mixed The formatted answer
     * @throws InvalidArgumentException Thrown if the answer is not of the correct type
     */
    public function formatAnswer($answer)
    {
        // By default, just return the answer
        return $answer;
    }
}
