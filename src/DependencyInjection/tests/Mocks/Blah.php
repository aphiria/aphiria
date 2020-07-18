<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks another class that implements a simple interface
 */
class Blah implements IFoo
{
    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
