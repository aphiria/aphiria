<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Application\Builders;

use Aphiria\Application\Builders\IModuleBuilder;

/**
 * Defines a base module builder that can build Aphiria components
 */
abstract class ModuleBuilder implements IModuleBuilder
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
