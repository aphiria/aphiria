<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands\Mocks;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Prompts\Prompt;
use Aphiria\Console\Prompts\Questions\Question;
use Aphiria\Console\Responses\IResponse;

/**
 * Mocks a command with multiple prompts
 */
class MultiplePromptsCommand extends Command
{
    /** @var Prompt The prompt to use */
    private $prompt;

    /**
     * @param Prompt $prompt The prompt to use
     */
    public function __construct(Prompt $prompt)
    {
        parent::__construct();

        $this->prompt = $prompt;
    }

    /**
     * @inheritdoc
     */
    protected function define(): void
    {
        $this->setName('multipleprompts');
        $this->setDescription('Asks multiple questions');
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response): ?int
    {
        $question1 = new Question('Q1', 'default1');
        $question2 = new Question('Q2', 'default2');
        $answer1 = $this->prompt->ask($question1, $response);
        $answer2 = $this->prompt->ask($question2, $response);

        if ($answer1 === 'default1') {
            $response->write('Default1');
        } else {
            $response->write('Custom1');
        }

        if ($answer2 === 'default2') {
            $response->write('Default2');
        } else {
            $response->write('Custom2');
        }

        return null;
    }
}
