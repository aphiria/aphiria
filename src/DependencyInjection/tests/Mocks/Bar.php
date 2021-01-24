<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class that implements a simple interface
 */
class Bar implements IFoo
{
    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
