<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    public function destroy($session_id): bool
    {
        $this->driver->delete($session_id);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function gc($maxlifetime): bool
    {
        $this->driver->gc($maxlifetime);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function open($save_path, $name): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($session_id): string
    {
        try {
            $sessionData = $this->driver->get($session_id);

            return $this->encrypter === null ? $sessionData : $this->encrypter->decrypt($sessionData);
        } catch (SessionEncryptionException) {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function write($session_id, $session_data): bool
    {
        try {
            $sessionDataToWrite = $this->encrypter === null ? $session_data : $this->encrypter->encrypt($session_data);
            $this->driver->set($session_id, $sessionDataToWrite);

            return true;
        } catch (SessionEncryptionException) {
            return false;
        }
    }
}
