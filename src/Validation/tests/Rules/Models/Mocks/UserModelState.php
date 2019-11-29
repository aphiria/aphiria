<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Rules\Models\Mocks;

use Aphiria\Validation\IValidator;
use Aphiria\Validation\Models\ModelState;

/**
 * Mocks the user model state for use in testing
 */
class UserModelState extends ModelState
{
    /**
     * @inheritdoc
     * @param User $model
     */
    protected function getModelProperties($model): array
    {
        return [
            'id' => $model->getId(),
            'email' => $model->getEmail(),
            'name' => $model->getName()
        ];
    }

    /**
     * @inheritdoc
     */
    protected function registerFields(IValidator $validator): void
    {
        $validator->field('id')
            ->integer();
        $validator->field('name')
            ->required();
        $validator->field('email')
            ->email();
    }
}
