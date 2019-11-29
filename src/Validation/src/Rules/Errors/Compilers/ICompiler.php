<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Rules\Errors\Compilers;

/**
 * Defines the interface for error template compilers to implement
 */
interface ICompiler
{
    /**
     * Compiles an error template
     *
     * @param string $field The name of the field whose template we're compiling
     * @param string $template The uncompiled template
     * @param array $args The arguments used by the rule
     * @return string The compiled error template
     */
    public function compile(string $field, string $template, array $args = []): string;
}
