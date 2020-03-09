<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Application;

use Aphiria\Application\IModule;
use Aphiria\Framework\Application\Builders\AphiriaComponentBuilder;

/**
 * Defines a base module that can build Aphiria components
 */
abstract class Module implements IModule
{
    /** @var AphiriaComponentBuilder The Aphiria component builder */
    protected AphiriaComponentBuilder $aphiriaComponentBuilder;

    /**
     * @param AphiriaComponentBuilder $aphiriaComponentBuilder The Aphiria component builder
     */
    public function __construct(AphiriaComponentBuilder $aphiriaComponentBuilder)
    {
        $this->aphiriaComponentBuilder = $aphiriaComponentBuilder;
    }
}
