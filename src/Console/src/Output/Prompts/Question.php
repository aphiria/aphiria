<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Prompts;

use InvalidArgumentException;

/**
 * Defines a console prompt question
 */
class Question
{
    /**
     * @param string $text The question text
     * @param mixed $defaultAnswer The default answer to the question
     * @param bool $isHidden Whether or not the answer should be hidden
     */
    public function __construct(public string $text, public mixed $defaultAnswer = null, public bool $isHidden = false)
    {
    }

    /**
     * Formats an answer
     * Useful for subclasses to override
     *
     * @param mixed $answer The answer to format
     * @return mixed The formatted answer
     * @throws InvalidArgumentException Thrown if the answer is not of the correct type
     */
    public function formatAnswer(mixed $answer): mixed
    {
        // By default, just return the answer
        return $answer;
    }
}
