<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Routing;

final class UriMatcher
{
    /**
     * Returns TRUE if provided paths combination matches request URI
     *
     * @param string $uri '/clients/1/address'
     * @param string $resourcePath '/clients/<REGEX>'
     * @param string $operationPath '/address'
     * @param mixed[]|null $matches
     */
    public static function matchUri(string $uri, string $resourcePath, string $operationPath, &$matches = null): bool
    {
        $uri = self::normalizeUri($uri);
        $path = self::normalizeUri($resourcePath . $operationPath);

        return (bool) preg_match("#^{$path}$#", $uri, $matches);
    }

    private static function normalizeUri(string $uri): string
    {
        return rtrim(str_replace('//', '/', $uri), '/');
    }
}
