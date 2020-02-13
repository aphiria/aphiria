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
    public function formatAnswer($answer): bool
    {
        if (is_bool($answer)) {
            return $answer;
        }

        // Accept anything that begins with "y" like "y", "yes", and "YES"
        return mb_strtolower($answer[0]) === 'y';
    }
}
