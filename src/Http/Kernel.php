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
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Kernel
{
    private ?ContainerBuilder $container;
    private string $env;
    private bool $booted = false;

    /**
     * @param string $env
     * @return Kernel
     * @throws \Exception
     */
    public function boot(string $env = 'dev'): self
    {
        $this->env = $env;

        $this->initializeContainer();
        $this->booted = true;

        return $this;
    }

    /**
     * @return Kernel
     */
    public function shutdown(): self
    {
        $this->container = null;
        $this->booted = false;

        return $this;
    }

    /**
     * @return ContainerInterface
     * @throws \RuntimeException if kernel is not booted
     */
    public function getContainer(): ContainerInterface
    {
        if (false === $this->booted) {
            throw new \RuntimeException('Unable to obtain container when kernel is not booted. Please, boot kernel first.');
        }

        return $this->container;
    }

    /**
     * @param Request $request
     * @return Response
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
        } catch (HttpException $e) {
            return $formatter->format(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Throwable $e) {
            if ('prod' === $this->env) {
                return $formatter->format(['error' => 'Internal server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            throw $e;
        }

        return $formatter->format($output, $operation->getResponseStatus());
    }

    /**
     * @return FileLocatorInterface
     */
    abstract public function getConfigLocator(): FileLocatorInterface;

    /**
     * Loads external configuration files
     */
    abstract protected function loadConfiguration(): void;

    /**
     * Initializes DI container
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
