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

use Aphiria\DependencyInjection\IServiceResolver;

// Make the service resolver return the same type as the input parameter
override(IServiceResolver::resolve(), type(0));
