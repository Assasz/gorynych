<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Symfony\Component\HttpFoundation\Response;

final class KernelClient
{
    private Kernel $kernel;
    private RequestFactoryInterface $requestFactory;
    private ?Response $response;

    public function __construct(Kernel $kernel, RequestFactoryInterface $requestFactory)
    {
        $this->kernel = $kernel;
        $this->requestFactory = $requestFactory;
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
        return $this->response = $this->kernel
            ->reboot()
            ->handleRequest(
                $this->requestFactory->create($method, $uri, $options)
            );
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
