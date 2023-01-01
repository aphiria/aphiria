<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Sessions\Handlers;

/**
 * Defines the interface for session encrypters to implement
 */
interface ISessionEncrypter
{
    /**
     * Decrypts the data
     *
     * @param string $data The data to decrypt
     * @return string The decrypted data
     * @throws SessionEncryptionException Thrown if there was an error decrypting the data
     */
    public function decrypt(string $data): string;

    /**
     * Encrypts the data
     *
     * @param string $data The data to encrypt
     * @return string The encrypted data
     * @throws SessionEncryptionException Thrown if there was an error encrypting the data
     */
    public function encrypt(string $data): string;
}
