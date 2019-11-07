<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection;

use Throwable;

/**
 * Defines an exception that's thrown when a dependency could not be resolved
 */
final class ResolutionException extends DependencyInjectionException
{
    /** @var string The name of the interface that could not be resolved */
    private string $interface;
    /** @var string|null The target class of the interface, or null if there is no target */
    private ?string $targetClass;

    /**
     * @inheritdoc
     * @param string $commandHandlerClassName The name of the interface that could not be resolved
     * @param string|null $targetClass The target class of the interface, or null if there is no target
     */
    public function __construct(string $commandHandlerClassName, ?string $targetClass, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->interface = $commandHandlerClassName;
        $this->targetClass = $targetClass;
    }

    /**
     * Gets the interface that could not be resolved
     *
     * @return string The interface that could not be resolved
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * Gets the target class that failed
     *
     * @return string|null The target class of the interface, or null if there is no target
     */
    public function getTargetClass(): ?string
    {
        return $this->targetClass;
    }
}
