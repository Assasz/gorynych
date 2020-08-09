<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Schema;

final class SchemaFactory
{
    /**
     * Creates JSON schema from entire project
     */
    public function createFromProject(): OpenApi
    {
        return \OpenApi\scan([
            EnvAccess::get('PROJECT_DIR') . '/src',
            EnvAccess::get('PROJECT_DIR') . '/config',
        ]);
    }

    /**
     * Creates JSON schema from single file
     *
     * @throws \RuntimeException
     */
    public function createFromFile(string $file, Analysis $analysis = null): Schema
    {
        $schemas = \OpenApi\scan($file, ['analysis' => $analysis ?? new Analysis()])
            ->components
            ->schemas;

        if (true === empty($schemas)) {
            throw new \RuntimeException('Invalid schema.');
        }

        return current($schemas);
    }
}
