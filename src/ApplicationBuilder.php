<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration;

use Closure;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;

/**
 * Defines an application builder
 */
abstract class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IBootstrapperRegistry The bootstrappers that will be passed to bootstrapper delegates */
    protected $bootstrappers;
    /** @var Closure[] The list of bootstrapper delegates */
    protected $bootstrapperDelegates = [];

    /**
     * @param IBootstrapperRegistry $bootstrappers The bootstrappers that will be passed to bootstrapper delegates
     */
    protected function __construct(IBootstrapperRegistry $bootstrappers)
    {
        $this->bootstrappers = $bootstrappers;
    }

    /**
     * @inheritdoc
     */
    public function withBootstrappers(Closure $delegate): IApplicationBuilder
    {
        $this->bootstrapperDelegates[] = $delegate;

        return $this;
    }

    /**
     * Runs the bootstrapper delegates
     */
    protected function runBootstrapperDelegates(): void
    {
        foreach ($this->bootstrapperDelegates as $bootstrapperDelegate) {
            $bootstrapperDelegate($this->bootstrappers);
        }
    }
}
