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
        if (false === $this->booted) {
            throw new KernelNotBootedException('Unable to obtain container when kernel is not booted. Please, boot kernel first.');
        }

        return $this->container;
    }

    /**
     * @throws KernelNotBootedException
     * @throws \Throwable
     */
    public function handleRequest(Request $request): Response
    {
        if (false === $this->booted) {
            throw new KernelNotBootedException('Unable to handle request when kernel is not booted. Please, boot kernel first.');
        }

        try {
            $formatter = $this->initializeFormatterFactory()->create(...$request->getAcceptableContentTypes());
        } catch (NotAcceptableHttpException $e) {
            return new Response($e->getMessage(), $e->getStatusCode());
        }

        try {
            $operation = $this->initializeRouter()->findOperation($request);
            $output = $operation->handle($request);
        } catch (\Throwable $throwable) {
            if ('dev' === $this->env || ('test' === $this->env && !($throwable instanceof HttpException))) {
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

    protected function initializeRouter(): Router
    {
        return new Router(new ResourceLoader($this->getContainer(), $this->getConfigLocator()));
    }

    protected function initializeFormatterFactory(): FormatterFactory
    {
        return new FormatterFactory($this->getConfigLocator());
    }

    private function initializeContainer(): void
    {
        $this->container = new ContainerBuilder();

        $loader = new YamlFileLoader($this->container, new FileLocator(dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');

        $this->container->set('kernel.config_locator', $this->getConfigLocator());

        $this->loadConfiguration();
        $this->container->compile();
    }
}
