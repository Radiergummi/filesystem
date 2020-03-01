<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem;

use function array_filter;
use function implode;
use function preg_replace;
use function strpos;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;

/**
 * Normalizes a path, regardless of whether the path exists or not.
 *
 * @param string $path
 *
 * @return string
 * @see https://stackoverflow.com/a/14354948/2532203
 */
function normalizePath(string $path): string
{
    $patterns = ['~/{2,}~', '~/(\./)+~', '~([^/\.]+/(?R)*\.{2,}/)~', '~\.\./~'];
    $replacements = [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, '', ''];

    return preg_replace($patterns, $replacements, $path);
}

function pathStartsWith(string $path, string $prefix): bool
{
    $path = normalizePath($path);
    $prefix = normalizePath($prefix);

    return strpos($path, $prefix) !== false;
}

/**
 * Joins path segments
 *
 * @param string ...$segments
 *
 * @return string
 */
function joinPath(string ...$segments): string
{
    return implode(DIRECTORY_SEPARATOR, array_filter($segments));
}

function isWindows(): bool
{
    return PHP_OS_FAMILY === 'Windows';
}
