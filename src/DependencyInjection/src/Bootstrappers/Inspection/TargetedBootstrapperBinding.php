<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/dependency-injection/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers\Inspection;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;

/**
 * Defines a targeted binding
 */
final class TargetedBootstrapperBinding extends BootstrapperBinding
{
    /** @var string The targeted class */
    private string $targetClass;

    /**
     * @param string $targetClass The targeted class
     * @param string $interface The interface that is bound
     * @param Bootstrapper $bootstrapper The bootstrapper that registered the binding
     */
    public function __construct(string $targetClass, string $interface, Bootstrapper $bootstrapper)
    {
        parent::__construct($interface, $bootstrapper);

        $this->targetClass = $targetClass;
    }

    /**
     * Gets the target class
     *
     * @return string The target class
     */
    public function getTargetClass(): string
    {
        return $this->targetClass;
    }
}
