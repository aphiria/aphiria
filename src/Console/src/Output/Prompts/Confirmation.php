<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Prompts;

/**
 * Defines a confirmation question
 */
class Confirmation extends Question
{
    /**
     * @param string $text The question text
     * @param bool $defaultAnswer The default answer to the question
     */
    public function __construct(string $text, bool $defaultAnswer = true)
    {
        parent::__construct($text, $defaultAnswer);
    }

    /**
     * @inheritdoc
     */
    public function formatAnswer(mixed $answer): bool
    {
        if (\is_bool($answer)) {
            return $answer;
        }

        if (\is_int($answer)) {
            return $answer === 1;
        }

        if (\is_string($answer)) {
            // Accept anything that begins with "y" like "y", "yes", and "YES"
            return \mb_strtolower($answer[0]) === 'y';
        }

        return false;
    }
}
