<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Configuration\Mocks;

/**
 * Defines a dummy object for use in tests
 */
final class ConfigObject
{
    /** @var mixed[] The list of constructor params */
    public array $params;

    /**
     * @param mixed ...$params The constructor params
     */
    public function __construct(...$params)
    {
        $this->params = $params;
    }
}
