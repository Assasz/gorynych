<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class KernelClient
{
    public const JSON_BODY = 'json';

    private const DEFAULT_REQUEST_HEADERS = [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
    ];

    private Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Performs request on the kernel
     *
     * @param string $method
     * @param string $uri
     * @param string[][] $options
     * @return Response
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        return $this->kernel->handleRequest($this->prepareRequest($method, $uri, $options));
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string[][] $options
     * @return Request
     * @throws \RuntimeException
     */
    private function prepareRequest(string $method, string $uri, array $options = []): Request
    {
        if (false === array_key_exists('BASE_URI', $_ENV)) {
            throw new \RuntimeException('BASE_URI variable needs to be defined in your .env file.');
        }

        return Request::create(
            $_ENV['BASE_URI'] . $uri,
            $method,
            $options['parameters'] ?? [],
            $options['cookies'] ?? [],
            $options['files'] ?? [],
            array_merge(self::DEFAULT_REQUEST_HEADERS, $options['headers'] ?? []),
            $this->buildRequestBody($options)
        );
    }

    /**
     * @param string[][] $options
     * @return string|null
     */
    private function buildRequestBody(array $options): ?string
    {
        if (true === array_key_exists(self::JSON_BODY, $options)) {
            $body = json_encode($options[self::JSON_BODY]);
        }

        return $body ?? null;
    }
}
