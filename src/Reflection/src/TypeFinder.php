<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

use DirectoryIterator;
use InvalidArgumentException;
use IteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

/**
 * Defines the type finder that uses the file system to search for classes
 */
final class TypeFinder implements ITypeFinder
{
    /** @var int Will find all classes */
    private const TYPE_CLASS = 1;
    /** @var int Will find all interfaces */
    private const TYPE_INTERFACE = 2;
    /** @var int Will find all abstract classes */
    private const TYPE_ABSTRACT_CLASS = 4;

    /**
     * @inheritdoc
     */
    public function findAllClasses(string|array $directories, bool $recursive = false, bool $includeAbstractClasses = false): array
    {
        $typeFilter = $includeAbstractClasses ? self::TYPE_CLASS | self::TYPE_ABSTRACT_CLASS : self::TYPE_CLASS;

        return $this->findAllTypesWithFilter($directories, $recursive, $typeFilter);
    }

    /**
     * @inheritdoc
     */
    public function findAllInterfaces(string|array $directories, bool $recursive = false): array
    {
        return $this->findAllTypesWithFilter($directories, $recursive, self::TYPE_INTERFACE);
    }

    /**
     * @inheritdoc
     * @template T of object
     * @param class-string<T> $parentType The type whose sub-types we're searching for
     * @param string|list<string> $directories The path or list of paths of directories to search
     * @param bool $recursive Whether or not we want to recurse through all directories
     * @return list<class-string<T>> The list of all types that are sub-types of the input class/interface
     */
    public function findAllSubtypesOfType(string $parentType, string|array $directories, bool $recursive = false): array
    {
        $subTypes = [];

        foreach ($this->findAllTypes($directories, $recursive) as $type) {
            $reflectionType = new ReflectionClass($type);

            if ($reflectionType->isSubclassOf($parentType)) {
                /** @var class-string<T> $type */
                $subTypes[] = $type;
            }
        }

        return $subTypes;
    }

    /**
     * @inheritdoc
     */
    public function findAllTypes(string|array $directories, bool $recursive = false): array
    {
        $typeFilter = self::TYPE_CLASS | self::TYPE_INTERFACE | self::TYPE_ABSTRACT_CLASS;

        return $this->findAllTypesWithFilter($directories, $recursive, $typeFilter);
    }

    /**
     * Finds all types that pass a filter
     *
     * @param string|list<string> $directories The directory or directories to search through
     * @param bool $recursive Whether or not we should recursively find types
     * @param int $typeFilter The filter to apply (bitwise value of types defined in this class)
     * @return list<class-string> The list of types
     */
    private function findAllTypesWithFilter(string|array $directories, bool $recursive, int $typeFilter): array
    {
        if (\is_string($directories)) {
            $directories = [$directories];
        }

        $allTypes = [];

        foreach ($directories as $directory) {
            if (!\is_dir($directory)) {
                throw new InvalidArgumentException("$directory is not a directory");
            }

            if ($recursive) {
                $fileIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
            } else {
                $fileIter = new IteratorIterator(new DirectoryIterator($directory));
            }

            /** @var SplFileInfo $file */
            foreach ($fileIter as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $tokens = \token_get_all(\file_get_contents($file->getRealPath()));
                $allTypes = [...$allTypes, ...$this->getTypeFromTokens($tokens, $typeFilter)];
            }
        }

        return $allTypes;
    }

    /**
     * Gets the class names from a list of tokens
     * This will work even if multiple classes are defined in each file
     *
     * @param array<array{0: int, 1: string, 2: int}|string> $tokens The array of tokens
     * @param int $typeFilter The filter to apply (bitwise value of types defined in this class)
     * @return list<class-string> The names of the classes
     */
    private function getTypeFromTokens(array $tokens, int $typeFilter): array
    {
        $types = [];
        $numTokens = \count($tokens);
        $namespace = '';

        for ($i = 0;$i < $numTokens;$i++) {
            // Skip literals
            if (\is_string($tokens[$i])) {
                continue;
            }

            $className = $interfaceName = '';

            switch ($tokens[$i][0]) {
                case T_NAMESPACE:
                    $namespace = '';

                    // Ignore whitespace between the namespace keyword and the namespace itself
                    do {
                        $i++;
                    } while (isset($tokens[$i][0]) && $tokens[$i][0] === \T_WHITESPACE);

                    // Collect the namespace
                    while (isset($tokens[$i][0]) && \in_array($tokens[$i][0], [\T_NAME_FULLY_QUALIFIED, \T_NAME_QUALIFIED, \T_NS_SEPARATOR, \T_STRING], true)) {
                        $namespace .= $tokens[$i][1];
                        $i++;
                    }

                    break;
                case T_CLASS:
                    if (($typeFilter & self::TYPE_CLASS) === 0 && ($typeFilter & self::TYPE_ABSTRACT_CLASS) === 0) {
                        // We aren't trying to find a class, so break out
                        break;
                    }

                    // Scan previous tokens to see if they're double colons, which would mean this is a class constant
                    for ($j = $i - 1;$j >= 0;$j--) {
                        if ($tokens[$j][0] === T_DOUBLE_COLON) {
                            break 2;
                        }

                        if ($tokens[$j][0] === T_WHITESPACE) {
                            // Since we found whitespace, then we know this isn't a class constant
                            // Now, check if it's an abstract class
                            $isAbstract = isset($tokens[$j - 1][0]) && $tokens[$j - 1][0] === T_ABSTRACT;

                            if ($isAbstract && ($typeFilter & self::TYPE_ABSTRACT_CLASS) === 0) {
                                break 2;
                            }

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

                    $types[] = \ltrim($namespace . '\\' . $className, '\\');
                    break 2;
                case T_INTERFACE:
                    if (($typeFilter & self::TYPE_INTERFACE) === 0) {
                        break;
                    }

                    // Get the interface name
                    while (isset($tokens[++$i][1])) {
                        if ($tokens[$i][0] === T_STRING) {
                            $interfaceName .= $tokens[$i][1];
                            break;
                        }
                    }

                    $types[] = \ltrim($namespace . '\\' . $interfaceName, '\\');
                    break;
            }
        }

        /** @var list<class-string> $types */
        return $types;
    }
}
