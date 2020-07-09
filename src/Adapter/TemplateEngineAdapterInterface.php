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
     * @param string $template
     * @param array $parameters
     * @return string
     */
    public function render(string $template, array $parameters): string;
}
