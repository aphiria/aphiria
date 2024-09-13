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
    public int|string $id  {
        get => $this->id;
        set (mixed $value) {
            if ($this->idGenerator->idIsValid($value)) {
                $this->id = $value;
            } else {
                $this->regenerateId();
            }
        }
    }

    /**
     * @param int|string|null $id The Id of the session
     * @param IIdGenerator $idGenerator The Id generator to use, or null if using the default one
     */
    public function __construct(
        int|string|null $id = null,
        private readonly IIdGenerator $idGenerator = new UuidV4IdGenerator()
    ) {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function addManyVariables(array $variables): void
    {
        $this->variables = [...$this->variables, ...$variables];
    }

    /**
     * @inheritdoc
     */
    public function ageFlashData(): void
    {
        foreach ($this->getStaleFlashKeys() as $oldKey) {
            $this->delete($oldKey);
        }

        $this->setVariable(self::STALE_FLASH_KEYS_KEY, $this->getNewFlashKeys());
        $this->setVariable(self::NEW_FLASH_KEYS_KEY, []);
    }

    /**
     * @inheritdoc
     */
    public function containsKey(string $name): bool
    {
        return isset($this->variables[$name]);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $name): void
    {
        unset($this->variables[$name]);
    }

    /**
     * @inheritdoc
     */
    public function flash(string $name, $value): void
    {
        $this->setVariable($name, $value);
        $newFlashKeys = $this->getNewFlashKeys();
        $newFlashKeys[] = $name;
        $this->setVariable(self::NEW_FLASH_KEYS_KEY, $newFlashKeys);
        $staleFlashKeys = $this->getStaleFlashKeys();

        // Remove the data from the list of stale keys, if it was there
        if (($staleKey = \array_search($name, $staleFlashKeys, true)) !== false) {
            unset($staleFlashKeys[$staleKey]);
        }

        $this->setVariable(self::STALE_FLASH_KEYS_KEY, $staleFlashKeys);
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
    public function getVariable(string $name, mixed $defaultValue = null): mixed
    {
        return $this->variables[$name] ?? $defaultValue;
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
        return $this->getVariable((string)$offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            throw new InvalidArgumentException('Key cannot be empty');
        }

        $this->setVariable((string)$offset, $value);
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
        $this->setVariable(self::NEW_FLASH_KEYS_KEY, [...$newFlashKeys, ...$staleFlashKeys]);
        $this->setVariable(self::STALE_FLASH_KEYS_KEY, []);
    }

    /**
     * @inheritdoc
     */
    public function regenerateId(): void
    {
        $this->id = $this->idGenerator->generate();
    }

    /**
     * @inheritdoc
     */
    public function setVariable(string $name, $value): void
    {
        $this->variables[$name] = $value;
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
        return $this->getVariable(self::NEW_FLASH_KEYS_KEY, []);
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
        return $this->getVariable(self::STALE_FLASH_KEYS_KEY, []);
    }
}
