<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Tokenizers;

use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the array list input tokenizer
 */
final class ArrayListInputTokenizer implements IInputTokenizer
{
    /**
     * @inheritdoc
     * @param array{name: string, arguments: list<mixed>, options: list<mixed>} $input
     */
    public function tokenize(string|array $input): array
    {
        /** @psalm-suppress TypeDoesNotContainType The interface accepts wider types than this class */
        if (!\is_array($input)) {
            throw new InvalidArgumentException(self::class . ' only accepts arrays as input');
        }

        if (!isset($input['name'])) {
            throw new RuntimeException('No command name given');
        }

        if (!isset($input['arguments'])) {
            $input['arguments'] = [];
        }

        if (!isset($input['options'])) {
            $input['options'] = [];
        }

        return [$input['name'], ...$input['arguments'], ...$input['options']];
    }
}
