<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\ExtensionMethods\Components;

use Aphiria\Application\IComponent;
use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Closure;

/**
 * Defines the extension method component
 */
class ExtensionMethodComponent implements IComponent
{
    /**
     * @inheritdoc
     */
    public function build(): void
    {
    }

    /**
     * Registers an extension method
     *
     * @param class-string|list<class-string> $interfaces The interface or list of interfaces to register an extension method for
     * @param string $methodName The name of the extension method
     * @param Closure $closure The closure that will be invoked whenever the extension method will be called
     * @return static For chaining
     */
    public function withExtensionMethod(string|array $interfaces, string $methodName, Closure $closure): static
    {
        ExtensionMethodRegistry::registerExtensionMethod($interfaces, $methodName, $closure);

        return $this;
    }
}
