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
    private Environment $twig;

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

    private function setup(): void
    {
        $this->twig = new Environment(
            new FilesystemLoader(dirname(__DIR__, 2) . '/templates'), [
                'cache' => dirname(__DIR__, 2) . '/var/twig',
            ]
        );
    }
}
