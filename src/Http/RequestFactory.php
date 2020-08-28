<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Gorynych\Util\EnvAccess;
use Symfony\Component\HttpFoundation\Request;

final class RequestFactory
{
    public const REQUEST_JSON = 'json';

    private const DEFAULT_REQUEST_HEADERS = [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
    ];

    /**
     * @param string[][] $options
     */
    public function create(string $method, string $uri, array $options = []): Request
    {
        return Request::create(
            EnvAccess::get('BASE_URI') . $uri,
            $method,
            $options['parameters'] ?? [],
            $options['cookies'] ?? [],
            $options['files'] ?? [],
            array_merge(self::DEFAULT_REQUEST_HEADERS, $options['headers'] ?? []),
            $this->buildRequestBody($options),
        );
    }

    /**
     * @param string[][] $options
     */
    private function buildRequestBody(array $options): ?string
    {
        if (true === array_key_exists(self::REQUEST_JSON, $options)) {
            $body = json_encode($options[self::REQUEST_JSON]);
        }

        return $body ?? null;
    }
}
