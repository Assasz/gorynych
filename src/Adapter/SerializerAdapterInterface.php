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
     * Setups serializer
     *
     * @param string|null $definition Serializer definition name
     * @return mixed
     */
    public function setup(string $definition = null);

    /**
     * Normalizes provided data
     *
     * @param object|object[] $data
     * @param mixed[] $context
     * @return mixed[]
     */
    public function normalize($data, array $context = []): array;

    /**
     * Deserializes input data into specified object
     *
     * @param string $data
     * @param string $outputClass
     * @param string $format
     * @param mixed[] $context
     * @return object
     */
    public function deserialize(string $data, string $outputClass, string $format, array $context = []): object;
}
