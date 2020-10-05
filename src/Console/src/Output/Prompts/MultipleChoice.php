<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Prompts;

use InvalidArgumentException;

/**
 * Defines a multiple choice question
 */
class MultipleChoice extends Question
{
    /** @var string The string to display before the input */
    private string $answerLineString = '  > ';
    /** @var bool Whether or not to allow multiple choices */
    private bool $allowsMultipleChoices = false;

    /*
     * @param string $question The question text
     * @param array $choices The list of choices
     * @param mixed $defaultAnswer The default answer to the question
     */
    public function __construct(string $text, public array $choices, mixed $defaultAnswer = null)
    {
        parent::__construct($text, $defaultAnswer);
    }

    /**
     * @return bool
     */
    public function allowsMultipleChoices(): bool
    {
        return $this->allowsMultipleChoices;
    }

    /**
     * Gets whether or not the choices are an associative array
     *
     * @return bool True if the array is associative, otherwise false
     */
    public function choicesAreAssociative(): bool
    {
        return \count(array_filter(array_keys($this->choices), 'is_string')) > 0;
    }

    /**
     * @inheritdoc
     */
    public function formatAnswer($answer): mixed
    {
        $hasMultipleAnswers = false;
        $answer = str_replace(' ', '', (string)$answer);

        if (!str_contains($answer, ',')) {
            // The answer is not a list of answers
            $answers = [$answer];
        } else {
            if (!$this->allowsMultipleChoices) {
                throw new InvalidArgumentException('Multiple choices are not allowed');
            }

            $hasMultipleAnswers = true;
            $answers = explode(',', $answer);
        }

        if ($this->choicesAreAssociative()) {
            $selectedChoices = $this->getSelectedAssociativeChoices($answers);
        } else {
            $selectedChoices = $this->getSelectedIndexChoices($answers);
        }

        if (\count($selectedChoices) === 0) {
            throw new InvalidArgumentException('Invalid choice');
        }

        if ($hasMultipleAnswers) {
            return $selectedChoices;
        }

        return $selectedChoices[0];
    }

    /**
     * @return string
     */
    public function getAnswerLineString(): string
    {
        return $this->answerLineString;
    }

    /**
     * @param bool $allowsMultipleChoices
     */
    public function setAllowsMultipleChoices(bool $allowsMultipleChoices): void
    {
        $this->allowsMultipleChoices = $allowsMultipleChoices;
    }

    /**
     * @param string $answerLineString
     */
    public function setAnswerLineString(string $answerLineString): void
    {
        $this->answerLineString = $answerLineString;
    }

    /**
     * Gets the list of selected associative choices from a list of answers
     *
     * @param array $answers The list of answers
     * @return array The list of selected choices
     */
    private function getSelectedAssociativeChoices(array $answers): array
    {
        $selectedChoices = [];

        foreach ($answers as $answer) {
            if (\array_key_exists($answer, $this->choices)) {
                $selectedChoices[] = $this->choices[$answer];
            }
        }

        return $selectedChoices;
    }

    /**
     * Gets the list of selected indexed choices from a list of answers
     *
     * @param array $answers The list of answers
     * @return array The list of selected choices
     * @throws InvalidArgumentException Thrown if the answers are not of the correct type
     */
    private function getSelectedIndexChoices(array $answers): array
    {
        $selectedChoices = [];

        foreach ($answers as $answer) {
            if (!ctype_digit($answer)) {
                throw new InvalidArgumentException('Answer is not an integer');
            }

            $answer = (int)$answer;

            if ($answer < 1 || $answer > \count($this->choices)) {
                throw new InvalidArgumentException('Choice must be between 1 and ' . \count($this->choices));
            }

            // Answers are 1-indexed
            $selectedChoices[] = $this->choices[$answer - 1];
        }

        return $selectedChoices;
    }
}
