<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the POST route attribute
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Post extends Route
{
    /**
     * @inheritdoc
     */
    public function __construct(
        string $path = '',
        ?string $host = null,
        ?string $name = null,
        bool $isHttpsOnly = false,
        array $attributes = []
    ) {
        parent::__construct(['POST'], $path, $host, $name, $isHttpsOnly, $attributes);
    }
}
