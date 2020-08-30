<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing;

use Gorynych\Adapter\EntityManagerAdapterInterface;
use Gorynych\Http\Kernel;
use Gorynych\Http\KernelClient;
use Gorynych\Http\RequestFactory;
use Gorynych\Util\EnvAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ApiTestCase extends TestCase
{
    use ApiAssertionsTrait;
    use DatabaseRecreatableTrait;

    protected static ?ContainerInterface $container;
    protected static ?KernelClient $client;
    protected static ?EntityManagerAdapterInterface $entityManager;

    public function setUp(): void
    {
        $kernel = static::createKernel()->boot(EnvAccess::get('APP_ENV', 'test'));

        static::$container = $kernel->getContainer();
        static::$client = new KernelClient($kernel, new RequestFactory());
        static::$entityManager = static::$container->get('entity_manager.adapter');

        static::recreateDatabaseSchema();
    }

    public function tearDown(): void
    {
        static::dropDatabaseSchema();

        static::$container = static::$client = static::$entityManager = null;
    }

    /**
     * @return Kernel
     * @throws \RuntimeException if kernel cannot be retrieved
     */
    protected static function createKernel(): Kernel
    {
        $kernelClass = EnvAccess::get('KERNEL_CLASS');

        if (false === class_exists($kernelClass)) {
            throw new \RuntimeException('Unable to retrieve not existent application kernel.');
        }

        return new $kernelClass();
    }

    /**
     * @return mixed[]
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    protected static function normalizeResponse(): array
    {
        if (null === $response = static::$client->getResponse()) {
            throw new \BadMethodCallException('Cannot normalize empty response.');
        }

        $contentType = $response->headers->get('Content-Type');

        if ('application/json' === $contentType || 'application/problem+json' === $contentType) {
            $data = json_decode($response->getContent(), true);
        }

        if (false === isset($data) && false === empty($response->getContent())) {
            throw new \RuntimeException('Failed to normalize non empty response.');
        }

        return $data['data'] ?? $data ?? [];
    }
}
