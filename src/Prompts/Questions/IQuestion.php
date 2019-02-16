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
 * Defines the interface for questions to implement
 */
interface IQuestion
{
    /**
     * Formats an answer
     * Useful for subclasses to override
     *
     * @param mixed $answer The answer to format
     * @return mixed The formatted answer
     * @throws InvalidArgumentException Thrown if the answer is not of the correct type
     */
    public function formatAnswer($answer);

    /**
     * Gets the default answer
     *
     * @return mixed The default answer
     */
    public function getDefaultAnswer();

    /**
     * Gets the question text
     *
     * @return string The question text
     */
    public function getText(): string;
}
