<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use OpenApi\Annotations\OpenApi;

/**
 * Wrapper for Swagger OpenAPI scan function
 * @see \OpenApi\scan()
 */
final class OAReader
{
    public function read(): OpenApi
    {
        return \OpenApi\scan([
            EnvAccess::get('PROJECT_DIR') . '/src',
            EnvAccess::get('PROJECT_DIR') . '/config',
        ]);
    }
}
