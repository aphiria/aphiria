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
 * Mocks a command with a single prompt
 */
class SinglePromptCommand extends Command
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
        $this->setName('singleprompt');
        $this->setDescription('Asks a question');
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response): ?int
    {
        $question = new Question('What else floats', 'Very small rocks');
        $answer = $this->prompt->ask($question, $response);

        if ($answer === 'A duck') {
            $response->write('Very good');
        } else {
            $response->write('Wrong');
        }

        return null;
    }
}
