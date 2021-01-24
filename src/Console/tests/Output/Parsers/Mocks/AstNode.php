<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers\Mocks;

use Aphiria\Console\Output\Parsers\AstNode as BaseNode;

/**
 * Mocks a node for use in testing
 */
class AstNode extends BaseNode
{
    /**
     * @inheritdoc
     */
    public function isTag(): bool
    {
        return false;
    }
}
