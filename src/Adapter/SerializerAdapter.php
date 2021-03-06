<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

use Gorynych\Exception\NotDeserializableException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerAdapter
{
    /** @var SerializerInterface|NormalizerInterface */
    protected $serializer;
    protected FileLocatorInterface $configLocator;

    public function __construct(FileLocatorInterface $configLocator)
    {
        $this->configLocator = $configLocator;
    }

    /**
     * Normalizes provided data
     *
     * @param object|object[] $data
     * @param mixed[] $context
     * @return mixed[]
     */
    public function normalize($data, array $context = []): array
    {
        return $this->serializer->normalize($data, null, $context);
    }

    /**
     * Deserializes input data into specified object
     *
     * @param string $data
     * @param string $outputClass
     * @param string $format
     * @param mixed[] $context
     * @return object
     *
     * @throws NotDeserializableException
     */
    public function deserialize(string $data, string $outputClass, string $format, array $context = []): object
    {
        try {
            return $this->serializer->deserialize($data, $outputClass, $format, $context);
        } catch (NotEncodableValueException $e) {
            throw new NotDeserializableException($e->getMessage());
        }
    }

    /**
     * Returns TRUE is normalization for provided data representation can be processed
     *
     * @param mixed $data
     */
    public function canNormalize($data): bool
    {
        return (
            is_object($data) ||
            (is_array($data) && !empty($data) && is_object(current($data)))
        );
    }

    /**
     * Returns TRUE if deserialization for provided type can be processed
     */
    public function canDeserialize(string $type): bool
    {
        return Request::class !== $type && class_exists($type);
    }

    /**
     * Setups serializer
     *
     * @param string|null $definition Serializer definition name
     * @return self
     */
    public function setup(string $definition = null): self
    {
        if (false === empty($definition)) {
            $classMetadataFactory = new ClassMetadataFactory(new YamlFileLoader(
                $this->configLocator->locate("serializer/{$definition}.yaml")
            ));
        }

        $this->serializer = new Serializer(
            [new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory ?? null)],
            $this->getEncoders()
        );

        return $this;
    }

    /**
     * @return EncoderInterface[]
     */
    protected function getEncoders(): array
    {
        return [new JsonEncoder(), new XmlEncoder()];
    }
}
