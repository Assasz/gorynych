<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Symfony\Component\HttpFoundation\Request;

interface RequestFactoryInterface
{
    /**
     * @param string[][] $options
     */
    public function create(string $method, string $uri, array $options = []): Request;
}
