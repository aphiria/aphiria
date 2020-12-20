<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\ErrorMessages;

/**
 * Defines the default error message template registry where the error message template is the same as the ID
 */
final class DefaultErrorMessageTemplateRegistry implements IErrorMessageTemplateRegistry
{
    /**
     * @inheritdoc
     */
    public function getErrorMessageTemplate(string $errorMessageId, string $locale = null): string
    {
        return $errorMessageId;
    }
}
