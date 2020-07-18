<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http;

use Gorynych\Http\Exception\HttpException;
use Gorynych\Http\Exception\NotAcceptableHttpException;
use Gorynych\Http\Formatter\FormatterFactory;
use Gorynych\Resource\ResourceLoader;
use Gorynych\Util\ProblemDetails;
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
     * @throws \RuntimeException if kernel is not booted
     */
    public function getContainer(): ContainerBuilder
    {
        if (false === $this->booted) {
            throw new \RuntimeException('Unable to obtain container when kernel is not booted. Please, boot kernel first.');
        }

        return $this->container;
    }

    /**
     * @throws \RuntimeException if kernel is not booted
     * @throws \Throwable
     */
    public function handleRequest(Request $request): Response
    {
        if (false === $this->booted) {
            throw new \RuntimeException('Unable to handle request when kernel is not booted. Please, boot kernel first.');
        }

        $router = new Router(new ResourceLoader($this->getContainer(), $this->getConfigLocator()));
        $formatterFactory = new FormatterFactory($this->getConfigLocator());

        try {
            $formatter = $formatterFactory->create(...$request->getAcceptableContentTypes());
        } catch (NotAcceptableHttpException $e) {
            return new Response($e->getMessage(), $e->getStatusCode());
        }

        try {
            $operation = $router->findOperation($request);
            $output = $operation($request);
        } catch (\Throwable $t) {
            if ('dev' === $this->env || ('test' === $this->env && !($t instanceof HttpException))) {
                throw $t;
            }

            return $formatter->format(
                ProblemDetails::fromThrowable($t),
                $t instanceof HttpException ? $t->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR
            );
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

    /**
     * Initializes Dependency Injection container
     */
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
