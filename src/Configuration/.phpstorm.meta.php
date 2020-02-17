<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace PHPSTORM_META;

use Aphiria\Configuration\Builders\IApplicationBuilder;

// Make the application builder return the same type as the input parameter
override(IApplicationBuilder::getComponentBuilder(), type(0));
