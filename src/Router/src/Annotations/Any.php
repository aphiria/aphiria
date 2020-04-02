<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Defines the any-method route annotation
 * @Annotation
 * @Target({"METHOD"})
 */
final class Any extends Route
{
    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        // Explicitly unset any methods that might have been set to prevent odd behavior
        unset($values['httpMethods']);
        parent::__construct($values);
    }
}
