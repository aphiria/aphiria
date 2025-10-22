<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers\Delete;

use Aphiria\Application\Discoverers\IComponentBuilder;
use Exception;

class Builder implements IComponentBuilder
{
    public ?string $builtComponent = null;
    public string $componentName {
        get => 'app:foo';
    }

    /**
     * @inheritdoc
     */
    public function build(array $components): void
    {
        if (\count($components) !== 1) {
            throw new Exception('Expected 1 component, got ' . \count($components));
        }

        $this->builtComponent = $components[0]->class->name;
    }
}
