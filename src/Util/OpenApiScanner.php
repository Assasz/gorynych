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

final class OpenApiScanner
{
    /**
     * Scans Open API annotations across project source and config directories
     */
    public function scan(): OpenApi
    {
        return \OpenApi\scan([
            EnvAccess::get('PROJECT_DIR') . '/src',
            EnvAccess::get('PROJECT_DIR') . '/config',
        ]);
    }

    /**
     * Scans single file and returns its schema
     *
     * @throws \RuntimeException
     */
    public function scanFile(string $file, Analysis $analysis = null): Schema
    {
        $schemas = \OpenApi\scan($file, ['analysis' => $analysis ?? new Analysis()])
            ->components
            ->schemas;

        if (true === empty($schemas)) {
            throw new \RuntimeException('Non existent schema.');
        }

        return current($schemas);
    }
}
