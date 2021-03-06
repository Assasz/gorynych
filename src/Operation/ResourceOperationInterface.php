<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Operation;

use Gorynych\Resource\AbstractResource;
use Symfony\Component\HttpFoundation\Request;

interface ResourceOperationInterface
{
    /**
     * Returns HTTP method chosen to perform resource operation
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Returns operation path
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Returns HTTP status code, which is assumed as successful for this particular operation
     *
     * @return int
     */
    public function getResponseStatus(): int;

    /**
     * @return mixed|void
     */
    public function setResource(AbstractResource $resource);

    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request);
}
