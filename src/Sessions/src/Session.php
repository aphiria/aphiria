<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    public const NEW_FLASH_KEYS_KEY = '__APHIRIA_NEW_FLASH_KEYS';
    /** The key for stale flash keys */
    public const STALE_FLASH_KEYS_KEY = '__APHIRIA_STALE_FLASH_KEYS';

    /** @var int|string The session Id */
    private $id = '';
    /** @var IIdGenerator The Id generator to use */
    private IIdGenerator $idGenerator;
    /** @var array The mapping of variable names to values */
    private array $vars = [];

    /**
     * @param int|string|null $id The Id of the session
     * @param IIdGenerator $idGenerator The Id generator to use
     */
    public function __construct($id = null, IIdGenerator $idGenerator = null)
    {
        $this->idGenerator = $idGenerator ?? new UuidV4IdGenerator();
        $this->setId($id);
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
        return isset($this->vars[$key]);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $key): void
    {
        unset($this->vars[$key]);
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
        if (($staleKey = array_search($key, $staleFlashKeys, true)) !== false) {
            unset($staleFlashKeys[$staleKey]);
        }

        $this->set(self::STALE_FLASH_KEYS_KEY, $staleFlashKeys);
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        $this->vars = [];
    }

    /**
     * @inheritdoc
     */
    public function get(string $key, $defaultValue = null)
    {
        return $this->vars[$key] ?? $defaultValue;
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->vars;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($key): bool
    {
        return $this->containsKey($key);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($key, $value): void
    {
        if ($key === null) {
            throw new InvalidArgumentException('Key cannot be empty');
        }

        $this->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($key): void
    {
        unset($this->vars[$key]);
    }

    /**
     * @inheritdoc
     */
    public function reflash(): void
    {
        $newFlashKeys = $this->getNewFlashKeys();
        $staleFlashKeys = $this->getStaleFlashKeys();
        $this->set(self::NEW_FLASH_KEYS_KEY, array_merge($newFlashKeys, $staleFlashKeys));
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
        $this->vars[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setId($id): void
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
        $this->vars = array_merge($this->vars, $variables);
    }

    /**
     * Gets the new flash keys array
     *
     * @return array The list of new flashed keys
     */
    protected function getNewFlashKeys(): array
    {
        return $this->get(self::NEW_FLASH_KEYS_KEY, []);
    }

    /**
     * Gets the stale flash keys array
     *
     * @return array The list of stale flashed keys
     */
    protected function getStaleFlashKeys(): array
    {
        return $this->get(self::STALE_FLASH_KEYS_KEY, []);
    }
}
