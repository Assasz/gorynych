<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Gorynych\Util\EnvAccess;
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
    private ?Response $response;

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
        return $this->response = $this->kernel->reboot()->handleRequest($this->prepareRequest($method, $uri, $options));
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string[][] $options
     * @return Request
     */
    private function prepareRequest(string $method, string $uri, array $options = []): Request
    {
        return Request::create(
            EnvAccess::get('BASE_URI') . $uri,
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
