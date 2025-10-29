<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers\Mocks;

#[SomeAttribute]
class DiscoveredClass
{
    #[SomeAttribute]
    public string $someProperty;

    #[SomeAttribute]
    public function someMethod(): void {}
}
