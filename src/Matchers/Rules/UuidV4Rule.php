<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Rules;

/**
 * Defines the UUIDV4 rule
 */
class UuidV4Rule implements IRule
{
    /** @var string The UUIDV4 regex */
    private const UUIDV4_REGEX = '/^\{?[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}\}?$/i';

    /**
     * @inheritdoc
     */
    public static function getSlug(): string
    {
        return 'uuidv4';
    }

    /**
     * @inheritdoc
     */
    public function passes($value): bool
    {
        return preg_match(self::UUIDV4_REGEX, $value) === 1;
    }
}
