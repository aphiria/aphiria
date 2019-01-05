<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding\Mocks;

/**
 * Mocks a class with typed variadic params
 */
class ConstructorWithTypedVariadicParams
{
    private $users;

    public function __construct(User ...$users)
    {
        $this->users = $users;
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}
