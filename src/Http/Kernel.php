<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Gorynych\Exception\KernelNotBootedException;
use Gorynych\Http\Dto\ProblemDetails;
use Gorynych\Http\Exception\HttpException;
use Gorynych\Http\Exception\NotAcceptableHttpException;
use Gorynych\Http\Formatter\FormatterFactory;
use Gorynych\Http\Routing\Router;
use Gorynych\Resource\ResourceLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Kernel
{
    protected ?ContainerBuilder $container;
    protected string $env;
    protected bool $booted = false;

    public function boot(string $env = 'dev'): self
    {
        $this->env = $env;

        $this->initializeContainer();
        $this->booted = true;

        return $this;
    }

    public function reboot(): self
    {
        $this->shutdown()->boot($this->env ?? 'dev');

        return $this;
    }

    public function shutdown(): self
    {
        $this->container = null;
        $this->booted = false;

        return $this;
    }

    /**
     * @throws KernelNotBootedException
     */
    public function getContainer(): ContainerBuilder
    {
        $this->ensureIsBooted();

        return $this->container;
    }

    /**
     * @throws KernelNotBootedException
     */
    public function getRouter(): Router
    {
        $this->ensureIsBooted();

        return $this->container->get('http.router');
    }

    /**
     * @throws KernelNotBootedException
     */
    public function getFormatterFactory(): FormatterFactory
    {
        $this->ensureIsBooted();

        return $this->container->get('http.formatter_factory');
    }

    /**
     * @throws KernelNotBootedException
     * @throws \Throwable
     */
    public function handleRequest(Request $request): Response
    {
        $this->ensureIsBooted();

        try {
            $formatter = $this->getFormatterFactory()->create(...$request->getAcceptableContentTypes());
        } catch (NotAcceptableHttpException $e) {
            return new Response($e->getMessage(), $e->getStatusCode());
        }

        try {
            $operation = $this->getRouter()->findOperation($request);
            $output = $operation->handle($request);
        } catch (\Throwable $throwable) {
            if (
                'dev' === $this->env ||
                ('test' === $this->env && !($throwable instanceof HttpException))
            ) {
                throw $throwable;
            }

            $problemDetails = ProblemDetails::fromThrowable($throwable);

            return $formatter->format($problemDetails, $problemDetails->status);
        }

        return $formatter->format($output, $operation->getResponseStatus());
    }

    /**
     * Returns FileLocator for project config directory
     */
    abstract public function getConfigLocator(): FileLocatorInterface;

    /**
     * Loads project configuration files
     */
    abstract protected function loadConfiguration(): void;

    private function initializeContainer(): void
    {
        $this->container = new ContainerBuilder();

        $loader = new YamlFileLoader($this->container, new FileLocator(dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');

        $this->container->set('kernel.config_locator', $this->getConfigLocator());
        $this->container->set('http.router', new Router(new ResourceLoader($this->container, $this->getConfigLocator())));
        $this->container->set('http.formatter_factory', new FormatterFactory($this->getConfigLocator()));

        $this->loadConfiguration();
        $this->container->compile();
    }

    /**
     * @throws KernelNotBootedException
     */
    private function ensureIsBooted(): void
    {
        if (false === $this->booted) {
            throw new KernelNotBootedException();
        }
    }
}
