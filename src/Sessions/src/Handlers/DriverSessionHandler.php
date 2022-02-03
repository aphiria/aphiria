<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Handlers;

use SessionHandlerInterface;

/**
 * Defines a session handler backed by a driver
 */
final class DriverSessionHandler implements SessionHandlerInterface
{
    /**
     * @param ISessionDriver $driver The session driver to use
     * @param ISessionEncrypter|null $encrypter The optional encrypter to use for session data (null if not encrypting)
     */
    public function __construct(
        private readonly ISessionDriver $driver,
        private readonly ?ISessionEncrypter $encrypter = null
    ) {
    }

    /**
     * @inheritdoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function destroy(string $id): bool
    {
        $this->driver->delete($id);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function gc(int $max_lifetime): int|false
    {
        return $this->driver->gc($max_lifetime);
    }

    /**
     * @inheritdoc
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read(string $id): string
    {
        try {
            $sessionData = $this->driver->get($id);

            return $this->encrypter === null ? $sessionData : $this->encrypter->decrypt($sessionData);
        } catch (SessionEncryptionException) {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function write(string $id, string $data): bool
    {
        try {
            $sessionDataToWrite = $this->encrypter === null ? $data : $this->encrypter->encrypt($data);
            $this->driver->set($id, $sessionDataToWrite);

            return true;
        } catch (SessionEncryptionException) {
            return false;
        }
    }
}
