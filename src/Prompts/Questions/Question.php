<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Prompts\Questions;

use InvalidArgumentException;

/**
 * Defines a console prompt question
 */
class Question
{
    /** @var string The question text */
    public $text;
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
