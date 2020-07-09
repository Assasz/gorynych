<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Adapter;

interface TemplateEngineAdapterInterface
{
    /**
     * Renders given template
     *
     * @param string $template
     * @param mixed[] $parameters
     * @return string
     */
    public function render(string $template, array $parameters): string;
}
