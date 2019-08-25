<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers\Parsers\Mocks;

use Aphiria\Console\Output\Compilers\Parsers\AstNode as BaseNode;

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
