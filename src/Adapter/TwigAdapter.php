<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigAdapter
{
    protected Environment $twig;

    public function __construct()
    {
        $this->setup();
    }

    /**
     * Renders given template
     *
     * @param string $template
     * @param mixed[] $parameters
     * @return string
     */
    public function render(string $template, array $parameters): string
    {
        return $this->twig->render($template, $parameters);
    }

    /**
     * Setups Twig template engine
     */
    protected function setup(): void
    {
        $this->twig = new Environment(
            new FilesystemLoader($this->getGorynychTemplatesPath()), [
                'cache' => dirname(__DIR__, 2) . '/var/twig',
            ]
        );
    }

    /**
     * Returns path to Gorynych templates
     * This path must be included in setup process, if you want to take advantage of ApiGenerator
     *
     * @return string
     */
    final protected function getGorynychTemplatesPath(): string
    {
        return dirname(__DIR__, 2) . '/templates';
    }
}
