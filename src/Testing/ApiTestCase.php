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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ApiTestCase extends TestCase
{
    use ApiAssertionsTrait;
    use DatabaseRefreshableTrait;

    protected ?ContainerInterface $container;
    protected ?KernelClient $client;
    protected ?EntityManagerAdapterInterface $entityManager;

    public function setUp(): void
    {
        $kernel = static::createKernel()->boot($_ENV['APP_ENV'] ?? 'test');

        $this->container = $kernel->getContainer();
        $this->client = new KernelClient($kernel);

        /** @var EntityManagerAdapterInterface $entityManager */
        $entityManager = $this->container->get('entity_manager.adapter');
        $this->entityManager = $entityManager;

        $this->recreateDatabaseSchema();
    }

    public function tearDown(): void
    {
        $this->dropDatabaseSchema();

        $this->container = $this->client = $this->entityManager = null;
    }

    /**
     * @return Kernel
     * @throws \RuntimeException if kernel cannot be retrieved
     */
    protected static function createKernel(): Kernel
    {
        if (false === array_key_exists('KERNEL_CLASS', $_ENV)) {
            throw new \RuntimeException('Please, define KERNEL_CLASS variable in your .env file before you try to retrieve kernel.');
        }

        if (false === class_exists($_ENV['KERNEL_CLASS'])) {
            throw new \RuntimeException('Unable to retrieve not existent application kernel.');
        }

        return new $_ENV['KERNEL_CLASS']();
    }
}
