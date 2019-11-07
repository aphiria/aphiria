<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Annotations;

use Doctrine\Annotations\Annotation\Required;
use Doctrine\Annotations\Annotation\Target;
use InvalidArgumentException;

/**
 * Defines the middleware annotation
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
final class Middleware
{
    /**
     * The name of the middleware class
     *
     * @var string
     * @Required
     */
    public string $className;
    /** @var string[] The mapping of attribute names to values */
    public array $attributes = [];

    /**
     * @param array $values The mapping of value names to values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->className = $values['value'];
            unset($values['value']);
        }

        if (isset($values['className'])) {
            $this->className = $values['className'];
        }

        if (empty($this->className)) {
            throw new InvalidArgumentException('Class name must be set');
        }

        if (isset($values['attributes'])) {
            $this->attributes = $values['attributes'];
        }
    }
}
