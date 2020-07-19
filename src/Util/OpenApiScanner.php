<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use OpenApi\Annotations\OpenApi;

final class OpenApiScanner
{
    public static function scan(): OpenApi
    {
        return \OpenApi\scan([
            EnvAccess::get('PROJECT_DIR') . '/src',
            EnvAccess::get('PROJECT_DIR') . '/config',
        ]);
    }
}
