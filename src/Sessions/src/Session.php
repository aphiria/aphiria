<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions;

use Aphiria\Sessions\Ids\IIdGenerator;
use Aphiria\Sessions\Ids\UuidV4IdGenerator;
use InvalidArgumentException;

/**
 * Defines a session
 */
class Session implements ISession
{
    /** The key for new flash keys */
    public const string NEW_FLASH_KEYS_KEY = '__APHIRIA_NEW_FLASH_KEYS';
    /** The key for stale flash keys */
    public const string STALE_FLASH_KEYS_KEY = '__APHIRIA_STALE_FLASH_KEYS';
    /** @inheritdoc */
    public private(set) array $variables = [];

    /** @inheritdoc */
    public private(set) int|string $id = '';

    /**
     * @param int|string|null $id The Id of the session
     * @param IIdGenerator $idGenerator The Id generator to use, or null if using the default one
     */
    public function __construct(
        int|string|null $id = null,
        private readonly IIdGenerator $idGenerator = new UuidV4IdGenerator()
    ) {
        if ($id === null) {
            $this->regenerateId();
        } else {
            $this->setId($id);
        }
    }

    /**
     * @inheritdoc
     */
    public function ageFlashData(): void
    {
        foreach ($this->getStaleFlashKeys() as $oldKey) {
            $this->delete($oldKey);
        }

        $this->set(self::STALE_FLASH_KEYS_KEY, $this->getNewFlashKeys());
        $this->set(self::NEW_FLASH_KEYS_KEY, []);
    }

    /**
     * @inheritdoc
     */
    public function containsKey(string $key): bool
    {
        return isset($this->variables[$key]);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $key): void
    {
        unset($this->variables[$key]);
    }

    /**
     * @inheritdoc
     */
    public function flash(string $key, $value): void
    {
        $this->set($key, $value);
        $newFlashKeys = $this->getNewFlashKeys();
        $newFlashKeys[] = $key;
        $this->set(self::NEW_FLASH_KEYS_KEY, $newFlashKeys);
        $staleFlashKeys = $this->getStaleFlashKeys();

        // Remove the data from the list of stale keys, if it was there
        if (($staleKey = \array_search($key, $staleFlashKeys, true)) !== false) {
            unset($staleFlashKeys[$staleKey]);
        }

        $this->set(self::STALE_FLASH_KEYS_KEY, $staleFlashKeys);
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        $this->variables = [];
    }

    /**
     * @inheritdoc
     */
    public function get(string $key, mixed $defaultValue = null): mixed
    {
        return $this->variables[$key] ?? $defaultValue;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey((string)$offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            throw new InvalidArgumentException('Key cannot be empty');
        }

        $this->set((string)$offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->variables[(string)$offset]);
    }

    /**
     * @inheritdoc
     */
    public function reflash(): void
    {
        $newFlashKeys = $this->getNewFlashKeys();
        $staleFlashKeys = $this->getStaleFlashKeys();
        $this->set(self::NEW_FLASH_KEYS_KEY, [...$newFlashKeys, ...$staleFlashKeys]);
        $this->set(self::STALE_FLASH_KEYS_KEY, []);
    }

    /**
     * @inheritdoc
     */
    public function regenerateId(): void
    {
        $this->setId($this->idGenerator->generate());
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value): void
    {
        $this->variables[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setId(int|string $id): void
    {
        if ($this->idGenerator->idIsValid($id)) {
            $this->id = $id;
        } else {
            $this->regenerateId();
        }
    }

    /**
     * @inheritdoc
     */
    public function setMany(array $variables): void
    {
        $this->variables = [...$this->variables, ...$variables];
    }

    /**
     * Gets the new flash keys array
     *
     * @return list<string> The list of new flashed keys
     * @psalm-suppress MixedReturnStatement This will always return an array of strings
     * @psalm-suppress MixedInferredReturnType Ditto
     */
    protected function getNewFlashKeys(): array
    {
        return $this->get(self::NEW_FLASH_KEYS_KEY, []);
    }

    /**
     * Gets the stale flash keys array
     *
     * @return list<string> The list of stale flashed keys
     * @psalm-suppress MixedReturnStatement This will always return an array of strings
     * @psalm-suppress MixedInferredReturnType Ditto
     */
    protected function getStaleFlashKeys(): array
    {
        return $this->get(self::STALE_FLASH_KEYS_KEY, []);
    }
}
