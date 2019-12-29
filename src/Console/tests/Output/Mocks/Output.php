<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Mocks;

use Aphiria\Console\Output\Output as BaseOutput;

/**
 * Mocks the console output for use in tests
 */
class Output extends BaseOutput
{
    /**
     * Clears the output buffer
     */
    public function clear(): void
    {
        $this->write(chr(27) . '[2J' . chr(27) . '[;H');
    }

    /**
     * @inheritdoc
     */
    public function readLine(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function doWrite(string $message, bool $includeNewLine): void
    {
        echo $message . ($includeNewLine ? PHP_EOL : '');
    }
}
