<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

use Aphiria\Net\Http\IRequest;
use Aphiria\Security\IPrincipal;

/**
 * Defines the user accessor that uses request properties to store the user
 */
final class RequestPropertyUserAccessor implements IUserAccessor
{
    /** @const The key to store the user object in */
    private const string USER_KEY = '__aphiria_request_user';

    /**
     * @inheritdoc
     */
    public function getUser(IRequest $request): ?IPrincipal
    {
        $user = null;
        $request->getProperties()->tryGet(self::USER_KEY, $user);

        /** @var IPrincipal|null $user */
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function setUser(?IPrincipal $user, IRequest $request): void
    {
        $request->getProperties()->add(self::USER_KEY, $user);
    }
}
