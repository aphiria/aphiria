<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Defines a mock derived class
 */
final class DerivedClass extends BaseClass
{
    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return self::class;
    }
}
