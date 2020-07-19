<?php

namespace Gorynych\Util;

use OpenApi\Annotations\OpenApi;

final class OpenApiScanner
{
    /**
     * @throws \RuntimeException
     */
    public static function scan(): OpenApi
    {
        if (false === array_key_exists('PROJECT_DIR', $_ENV)) {
            throw new \RuntimeException('Please make sure that PROJECT_DIR environmental variable is defined.');
        }

        return \OpenApi\scan([
            "{$_ENV['PROJECT_DIR']}/src",
            "{$_ENV['PROJECT_DIR']}/config",
        ]);
    }
}
