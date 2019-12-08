<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules;

/**
 * Defines a base rule
 */
abstract class Rule implements IRule
{
    /** @var string The ID of the error message associated with this rule */
    protected string $errorMessageId;

    /**
     * @param string $errorMessageId The ID of the error message associated with this rule
     */
    public function __construct(string $errorMessageId)
    {
        $this->errorMessageId = $errorMessageId;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessageId(): string
    {
        return $this->errorMessageId;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessagePlaceholders(): array
    {
        // Let overriding implementations overrule this if there is something to return
        return [];
    }
}
