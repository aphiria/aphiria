<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the TRACE route attribute
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Trace extends Route
{
    /**
     * @inheritdoc
     */
    public function __construct(
        string $path = '',
        ?string $host = null,
        ?string $name = null,
        bool $isHttpsOnly = false,
        array $parameters = []
    ) {
        /** @psalm-suppress MixedArgumentTypeCoercion Psalm does not pass array types via inheritdoc (#4504) - bug */
        parent::__construct(['TRACE'], $path, $host, $name, $isHttpsOnly, $parameters);
    }
}
