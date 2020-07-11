<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Generator;

final class FileWriter
{
    private bool $overwrite = false;

    /**
     * Forces to overwrite file, but only in next write attempt
     *
     * @return $this
     */
    public function forceOverwrite(): self
    {
        $this->overwrite = true;

        return $this;
    }

    /**
     * Writes given content into specified file path
     *
     * @param string $path
     * @param string $content
     */
    public function write(string $path, string $content): void
    {
        $dir = dirname($path);

        if (false === is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (true === $this->overwrite || false === file_exists($path)) {
            file_put_contents($path, $content);
        }

        $this->overwrite = false;
    }
}
