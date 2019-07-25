<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Doctrine\Annotations\Annotation\Target;

/**
 * Defines the POST route annotation
 * @Annotation
 * @Target({"METHOD"})
 */
final class Post extends Route
{
    /**
     * @param array $values The values passed into the annotation
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        $this->httpMethods = ['POST'];
    }
}
