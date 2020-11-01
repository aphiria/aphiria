<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use ArrayIterator;
use RuntimeException;
use Traversable;

/**
 * Defines an immutable hash set
 */
class ImmutableHashSet implements IImmutableSet
{
    /** @var array<string, mixed> The set of values */
    protected array $values = [];
    /** @var KeyHasher The key hasher to use */
    private KeyHasher $keyHasher;

    /**
     * @param mixed[] $values The set of values
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function __construct(array $values)
    {
        $this->keyHasher = new KeyHasher();

        foreach ($values as $value) {
            $this->values[$this->getHashKey($value)] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function containsValue(mixed $value): bool
    {
        return isset($this->values[$this->getHashKey($value)]);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->values));
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return array_values($this->values);
    }

    /**
     * Gets the hash key for a value
     * This method allows extending classes to customize how hash keys are calculated
     *
     * @param mixed $value The value whose hash key we want
     * @return string The hash key
     * @throws RuntimeException Thrown if the hash key could not be calculated
     */
    protected function getHashKey(mixed $value): string
    {
        return $this->keyHasher->getHashKey($value);
    }
}
