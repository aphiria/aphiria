<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Aphiria\Console\Output\IOutput;

/**
 * Defines the Windows OS terminal driver
 */
class WindowsTerminalDriver extends TerminalDriver
{
    /**
     * @inheritdoc
     */
    public function readHiddenInput(IOutput $output): string
    {
        // TODO: Implement readHiddenInput() method.
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function getTerminalHeightFromOs(): ?int
    {
        // TODO: Implement getTerminalHeightFromOs() method.
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function getTerminalWidthFromOs(): ?int
    {
        // TODO: Implement getTerminalWidthFromOs() method.
        return null;
    }
}
