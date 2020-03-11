<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

/**
 * Defines the collection of bootstrappers
 */
final class BootstrapperCollection
{
    /** @var IBootstrapper[] The list of bootstrappers */
    private array $bootstrappers = [];

    /**
     * Adds a bootstrapper to the collection
     *
     * @param IBootstrapper $bootstrapper The bootstrapper to add
     * @return self For chaining
     */
    public function add(IBootstrapper $bootstrapper): self
    {
        $this->bootstrappers[] = $bootstrapper;

        return $this;
    }

    /**
     * Adds multiple bootstrappers to the collection
     *
     * @param IBootstrapper[] $bootstrappers The bootstrappers to add
     * @return self For chaining
     */
    public function addMany(array $bootstrappers): self
    {
        $this->bootstrappers = [...$this->bootstrappers, ...$bootstrappers];

        return $this;
    }

    public function bootstrapAll(): void
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap();
        }
    }
}
