<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection;

use Aphiria\DependencyInjection\Binders\Binder;

/**
 * Defines a targeted binding
 */
final class TargetedBinderBinding extends BinderBinding
{
    /** @var string The targeted class */
    private string $targetClass;

    /**
     * @param string $targetClass The targeted class
     * @param string $interface The interface that is bound
     * @param Binder $binder The binder that registered the binding
     */
    public function __construct(string $targetClass, string $interface, Binder $binder)
    {
        parent::__construct($interface, $binder);

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
