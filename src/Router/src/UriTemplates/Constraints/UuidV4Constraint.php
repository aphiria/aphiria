<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Constraints;

/**
 * Defines the UUIDV4 constraint
 */
final class UuidV4Constraint implements IRouteVariableConstraint
{
    /** @var string The UUIDV4 regex */
    private const UUIDV4_REGEX = '/^\{?[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}\}?$/i';

    /**
     * Gets the slug that will be used to actually add a constraint in a URI template
     *
     * @return string The slug used in the URI template
     */
    public static function getSlug(): string
    {
        return 'uuidv4';
    }

    /**
     * @inheritdoc
     */
    public function passes(mixed $value): bool
    {
        return \preg_match(self::UUIDV4_REGEX, (string)$value) === 1;
    }
}
