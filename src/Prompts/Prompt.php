<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Prompts;

use InvalidArgumentException;
use Aphiria\Console\Prompts\Questions\IQuestion;
use Aphiria\Console\Prompts\Questions\MultipleChoice;
use Aphiria\Console\Responses\Formatters\PaddingFormatter;
use Aphiria\Console\Responses\IResponse;
use RuntimeException;

/**
 * Defines a console prompt
 */
class Prompt
{
    /** @var PaddingFormatter The space padding formatter to use */
    private $paddingFormatter;
    /** @var resource The input stream to look for answers in */
    private $inputStream;

    /***
     * @param PaddingFormatter $paddingFormatter The space padding formatter to use
     * @param resource|null $inputStream The input stream to look for answers in
     */
    public function __construct(PaddingFormatter $paddingFormatter, $inputStream = null)
    {
        $this->paddingFormatter = $paddingFormatter;

        if ($inputStream === null) {
            $inputStream = STDIN;
        }

        $this->setInputStream($inputStream);
    }

    /**
     * Prompts the user to answer a question
     *
     * @param IQuestion $question The question to ask
     * @param IResponse $response The response to write output to
     * @return mixed The user's answer to the question
     * @throws RuntimeException Thrown if we failed to get the user's answer
     */
    public function ask(IQuestion $question, IResponse $response)
    {
        $response->write("<question>{$question->getText()}</question>");

        if ($question instanceof MultipleChoice) {
            /** @var MultipleChoice $question */
            $response->writeln('');
            $choicesAreAssociative = $question->choicesAreAssociative();
            $choiceTexts = [];

            foreach ($question->getChoices() as $key => $choice) {
                if (!$choicesAreAssociative) {
                    // Make the choice 1-indexed
                    ++$key;
                }

                $choiceTexts[] = [$key . ')', $choice];
            }

            $response->writeln($this->paddingFormatter->format($choiceTexts, function ($row) {
                return "  {$row[0]} {$row[1]}";
            }));
            $response->write($question->getAnswerLineString());
        }

        $answer = fgets($this->inputStream, 4096);

        if ($answer === false) {
            throw new RuntimeException('Failed to get answer');
        }

        $answer = trim($answer);

        if ($answer === '') {
            $answer = $question->getDefaultAnswer();
        }

        return $question->formatAnswer($answer);
    }

    /**
     * Sets the input stream
     *
     * @param resource $inputStream The input stream to look for answers in
     * @throws InvalidArgumentException Thrown if the input stream is not a resource
     */
    public function setInputStream($inputStream): void
    {
        if (!is_resource($inputStream)) {
            throw new InvalidArgumentException('Input stream must be resource');
        }

        $this->inputStream = $inputStream;
    }
}
