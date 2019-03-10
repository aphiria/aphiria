<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Prompts;

use Aphiria\Console\Output\Formatters\PaddingFormatter;
use Aphiria\Console\Output\IOutput;
use RuntimeException;

/**
 * Defines a console prompt
 */
class Prompt
{
    /** @var PaddingFormatter The space padding formatter to use */
    private $paddingFormatter;

    /***
     * @param PaddingFormatter|null $paddingFormatter The space padding formatter to use
     */
    public function __construct(PaddingFormatter $paddingFormatter = null)
    {
        $this->paddingFormatter = $paddingFormatter ?? new PaddingFormatter();
    }

    /**
     * Prompts the user to answer a question
     *
     * @param Question $question The question to ask
     * @param IOutput $output The output to write to
     * @return mixed The user's answer to the question
     * @throws RuntimeException Thrown if we failed to get the user's answer
     */
    public function ask(Question $question, IOutput $output)
    {
        $output->write("<question>{$question->text}</question>");

        if ($question instanceof MultipleChoice) {
            /** @var MultipleChoice $question */
            $output->writeln('');
            $choicesAreAssociative = $question->choicesAreAssociative();
            $choiceTexts = [];

            foreach ($question->choices as $key => $choice) {
                if (!$choicesAreAssociative) {
                    // Make the choice 1-indexed
                    ++$key;
                }

                $choiceTexts[] = [$key . ')', $choice];
            }

            $output->writeln($this->paddingFormatter->format($choiceTexts, function ($row) {
                return "  {$row[0]} {$row[1]}";
            }));
            $output->write($question->getAnswerLineString());
        }

        $answer = $output->readLine();

        if ($answer === false) {
            throw new RuntimeException('Failed to get answer');
        }

        $answer = trim($answer);

        if ($answer === '') {
            $answer = $question->defaultAnswer;
        }

        return $question->formatAnswer($answer);
    }
}
