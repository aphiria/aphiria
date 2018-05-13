<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization\Mocks;

use DateTime;

/**
 * Defines some imaginary subscription class for use in tests
 */
class Subscription
{
    /** @var int The subscription ID */
    private $id;
    /** @var DateTime|null The expiration, if there is one */
    private $expiration;

    /**
     * @param int $id The subscription ID
     * @param DateTime|null $expiration The expiration, if there is one
     */
    public function __construct(int $id, ?DateTime $expiration)
    {
        $this->id = $id;
        $this->expiration = $expiration;
    }

    /**
     * Gets the subscription's expiration
     *
     * @return DateTime|null The expiration, if there is one
     */
    public function getExpiration(): ?DateTime
    {
        return $this->expiration;
    }

    /**
     * Gets the subscription ID
     *
     * @return int The subscription ID
     */
    public function getId(): int
    {
        return $this->id;
    }
}
