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

use Aphiria\DependencyInjection\IServiceResolver;

// Make the dependency resolver return the same type as the input parameter
override(IServiceResolver::resolve(), type(0));
