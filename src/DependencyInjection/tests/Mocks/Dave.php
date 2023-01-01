<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Defines a class that implements an interface for use in IoC tests
 */
class Dave implements IPerson
{
    /**
     * @inheritdoc
     */
    public function getLastName(): string
    {
        return 'Young';
    }
}
