<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Psr11;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Psr\Container\ContainerInterface;

/**
 * Defines a PSR-11-compliant container
 */
class Psr11Container implements ContainerInterface
{
    /** @var IContainer The Aphiria container */
    private IContainer $container;

    /**
     * @param IContainer $container The Aphiria container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     * @throws NotFoundException Thrown if there was no binding for the ID
     * @throws ContainerException Thrown if there was an error auto-wiring the ID
     */
    public function get($id): object
    {
        try {
            return $this->container->resolve($id);
        } catch (ResolutionException $ex) {
            if ($this->container->hasBinding($id)) {
                throw new NotFoundException("No binding found for $id", 0, $ex);
            }

            throw new ContainerException("Failed to resolve $id", 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function has($id): bool
    {
        try {
            $this->get($id);

            return true;
        } catch (NotFoundException | ContainerException $ex) {
            return false;
        }
    }
}
