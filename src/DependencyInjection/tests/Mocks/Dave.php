<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/dependency-injection/blob/master/LICENSE.md
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
