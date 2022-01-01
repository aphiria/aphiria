<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Mocks;

/**
 * Mocks a class with a static setter method
 */
class StaticSetters
{
    /** @var IPerson|null A static dependency */
    public static ?IPerson $staticDependency = null;

    /**
     * @param IPerson $setterDependency
     */
    public static function setStaticSetterDependency(IPerson $setterDependency): void
    {
        self::$staticDependency = $setterDependency;
    }
}
