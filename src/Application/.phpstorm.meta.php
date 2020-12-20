<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace PHPSTORM_META;

use Aphiria\Application\Builders\IApplicationBuilder;

// Make the application builder return the same type as the input parameter
override(IApplicationBuilder::getComponent(), type(0));
