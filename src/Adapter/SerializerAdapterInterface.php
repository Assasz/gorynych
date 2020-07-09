<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

interface SerializerAdapterInterface
{
    /**
     * @param string|null $definition
     * @return mixed
     */
    public function setup(string $definition = null);

    /**
     * @param object|object[] $data
     * @param array $context
     * @return array
     */
    public function normalize($data, array $context = []): array;

    /**
     * @param string $data
     * @param string $outputClass
     * @param string $format
     * @param array $context
     * @return object
     */
    public function deserialize(string $data, string $outputClass, string $format, array $context = []): object;
}
