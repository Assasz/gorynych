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
use Gorynych\Util\EnvAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ApiTestCase extends TestCase
{
    use ApiAssertionsTrait;
    use DatabaseRefreshableTrait;

    protected static ?ContainerInterface $container;
    protected static ?KernelClient $client;
    protected static ?EntityManagerAdapterInterface $entityManager;

    public function setUp(): void
    {
        $kernel = static::createKernel()->boot(EnvAccess::get('APP_ENV', 'test'));

        static::$container = $kernel->getContainer();
        static::$client = new KernelClient($kernel);

        /** @var EntityManagerAdapterInterface $entityManager */
        $entityManager = static::$container->get('entity_manager.adapter');
        static::$entityManager = $entityManager;

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
     */
    protected static function normalizeResponse(): array
    {
        if (null === $response = static::$client->getResponse()) {
            throw new \BadMethodCallException('Cannot normalize empty response.');
        }

        $data = json_decode($response->getContent(), true);

        return $data['data'] ?? $data;
    }
}
