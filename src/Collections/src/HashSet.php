<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use ArrayIterator;
use RuntimeException;
use Traversable;

/**
 * Defines a hash set
 */
class HashSet implements ISet
{
    /** @var array<string, mixed> The set of values */
    protected array $values = [];
    /** @var KeyHasher The key hasher to use */
    private KeyHasher $keyHasher;

    /**
     * @param list<mixed> $values The set of values
     */
    public function __construct(array $values = [])
    {
        $this->keyHasher = new KeyHasher();
        $this->addRange($values);
    }

    /**
     * @inheritdoc
     */
    public function add(mixed $value): void
    {
        $this->values[$this->getHashKey($value)] = $value;
    }

    /**
     * @inheritdoc
     */
    public function addRange(array $values): void
    {
        /** @psalm-suppress MixedAssignment We are purposely adding mixed values */
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->values = [];
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
        return new ArrayIterator(\array_values($this->values));
    }

    /**
     * @inheritdoc
     */
    public function intersect(array $values): static
    {
        $intersectedValues = [];

        // We don't use array_intersect because that does string comparisons, which requires __toString()
        /** @psalm-suppress MixedAssignment We are purposely adding mixed values */
        foreach ($this->values as $value) {
            if (\in_array($value, $values, true)) {
                $intersectedValues[] = $value;
            }
        }

        return new static($intersectedValues);
    }

    /**
     * @inheritdoc
     */
    public function removeValue(mixed $value): void
    {
        unset($this->values[$this->getHashKey($value)]);
    }

    /**
     * @inheritdoc
     */
    public function sort(callable $comparer): static
    {
        // Get a copy of the values
        $values = $this->values;
        /** @psalm-suppress InvalidPropertyAssignmentValue This is valid - bug */
        \usort($values, $comparer);

        return new static($values);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return \array_values($this->values);
    }

    /**
     * @inheritdoc
     */
    public function union(array $values): static
    {
        return new static(\array_merge(\array_values($this->values), $values));
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
