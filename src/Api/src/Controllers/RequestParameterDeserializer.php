<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Closure;

class RequestParameterDeserializer implements IRequestParameterDeserializer
{
    /**
     * @var array<string, Closure(mixed): mixed> The map of types to their deserializers
     */
    private array $typesToDeserializers = [];

    /**
     * @param array<string, Closure(mixed): mixed> $typesToDeserializers The map of types to their deserializers
     */
    public function __construct(?array $typesToDeserializers = null)
    {
        $this->typesToDeserializers = $typesToDeserializers ?? [
            'bool' => function (mixed $value): bool {
                return match ($value) {
                    true, '1', 1, 'true', 'yes', 'y' => true,
                    default => false,
                };
            },
            'float' => fn(mixed $value): float => (float) $value,
            'int' => fn(mixed $value): int => (int) $value,
            'string' => fn(mixed $value): string => (string) $value,
        ];
    }

    /**
     * @inheritdoc
     */
    public function deserializeRouteActionParameter(string $type, mixed $value): mixed
    {
        if (!isset($this->typesToDeserializers[$type])) {
            throw new FailedRequestParameterConversionException("No deserializer registered for type $type");
        }

        return $this->typesToDeserializers[$type]($value);
    }

    /**
     * Registers a deserializer
     *
     * @param string $type The type that will be deserialized
     * @param Closure(mixed): mixed $deserializer The deserializer
     */
    public function registerDeserializer(string $type, Closure $deserializer): void
    {
        $this->typesToDeserializers[$type] = $deserializer;
    }
}
