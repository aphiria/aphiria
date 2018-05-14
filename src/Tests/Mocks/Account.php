<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Mocks;

/**
 * Defines an account for use in tests
 */
class Account
{
    /** @var int The ID of the account */
    private $id;
    /** @var Subscription[] The list of subscriptions the account has */
    private $subscriptions;

    /**
     * @param int $id The ID of the account
     * @param Subscription[] $subscriptions The list of subscriptions the account has
     */
    public function __construct(int $id, array $subscriptions)
    {
        $this->id = $id;
        $this->subscriptions = $subscriptions;
    }

    /**
     * Gets the account ID
     *
     * @return int The ID of the account
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the subscriptions this account has
     *
     * @return Subscription[] The list of subscriptions
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
