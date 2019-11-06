<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

use DirectoryIterator;
use InvalidArgumentException;
use IteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Defines the class finder that uses the file system to search for classes
 */
final class FileClassFinder implements IClassFinder
{
    /**
     * @inheritdoc
     */
    public function findAllClasses($directories, bool $recursive = false): array
    {
        if (is_string($directories)) {
            $directories = [$directories];
        }

        if (!is_array($directories)) {
            throw new InvalidArgumentException('Paths must be a string or array of strings');
        }

        $allClassNames = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                throw new InvalidArgumentException("Path $directory is not a directory");
            }

            if ($recursive) {
                $fileIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            } else {
                $fileIter = new IteratorIterator(new DirectoryIterator($directory));
            }

            foreach ($fileIter as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $tokens = token_get_all(file_get_contents($file->getRealPath()));
                $allClassNames = [...$allClassNames, ...$this->getClassNamesFromTokens($tokens)];
            }
        }

        return $allClassNames;
    }

    /**
     * Gets the class names from a list of tokens
     * This will work even if multiple classes are defined in each file
     *
     * @param string[] $tokens The array of tokens
     * @return string[] The names of the classes
     */
    private function getClassNamesFromTokens(array $tokens): array
    {
        $classNames = [];
        $numTokens = count($tokens);
        $namespace = '';

        for ($i = 0;$i < $numTokens;$i++) {
            // Skip literals
            if (is_string($tokens[$i])) {
                continue;
            }

            $className = '';

            switch ($tokens[$i][0]) {
                case T_NAMESPACE:
                    $namespace = '';

                    // Collect all the namespace parts and separators
                    while (isset($tokens[++$i][1])) {
                        if (in_array($tokens[$i][0], [T_NS_SEPARATOR, T_STRING], true)) {
                            $namespace .= $tokens[$i][1];
                        }
                    }

                    break;
                case T_CLASS:
                    // Scan previous tokens to see if they're double colons, which would mean this is a class constant
                    for ($j = $i - 1;$j >= 0;$j--) {
                        if (!isset($tokens[$j][1])) {
                            break;
                        }

                        if ($tokens[$j][0] === T_DOUBLE_COLON) {
                            break 2;
                        }

                        if ($tokens[$j][0] === T_WHITESPACE) {
                            // Since we found whitespace, then we know this isn't a class constant
                            break;
                        }
                    }

                    // Get the class name
                    while (isset($tokens[++$i][1])) {
                        if ($tokens[$i][0] === T_STRING) {
                            $className .= $tokens[$i][1];
                            break;
                        }
                    }

                    $classNames[] = ltrim($namespace . '\\' . $className, '\\');
                    break 2;
            }
        }

        return $classNames;
    }
}
