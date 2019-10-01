<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Handlers;

use SessionHandlerInterface;

/**
 * Defines a session handler backed by a driver
 */
final class DriverSessionHandler implements SessionHandlerInterface
{
    /** @var ISessionDriver The session driver to use */
    private ISessionDriver $driver;
    /** @var ISessionEncrypter|null The optional encrypter to use for session data */
    private ?ISessionEncrypter $encrypter;

    /**
     * @param ISessionDriver $driver The session driver to use
     * @param ISessionEncrypter|null $encrypter The optional encrypter to use for session data (null if not encrypting)
     */
    public function __construct(ISessionDriver $driver, ISessionEncrypter $encrypter = null)
    {
        $this->driver = $driver;
        $this->encrypter = $encrypter;
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
    public function destroy($sessionId): bool
    {
        $this->driver->delete($sessionId);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function gc($maxLifetime): bool
    {
        $this->driver->gc($maxLifetime);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($sessionId): string
    {
        try {
            $sessionData = $this->driver->get($sessionId);

            return $this->encrypter === null ? $sessionData : $this->encrypter->decrypt($sessionData);
        } catch (SessionEncryptionException $ex) {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function write($sessionId, $sessionData): bool
    {
        try {
            $sessionDataToWrite = $this->encrypter === null ? $sessionData : $this->encrypter->encrypt($sessionData);
            $this->driver->set($sessionId, $sessionDataToWrite);

            return true;
        } catch (SessionEncryptionException $ex) {
            return false;
        }
    }
}
