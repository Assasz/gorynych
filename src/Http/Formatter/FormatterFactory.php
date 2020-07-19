<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Formatter;

use Gorynych\Http\Exception\NotAcceptableHttpException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Yaml\Yaml;

final class FormatterFactory
{
    /** @var string[] */
    private array $formatters;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->formatters = $this->parseFormatters($fileLocator->locate('formatters.yaml'));
    }

    /**
     * Creates formatter for first acceptable format
     *
     * @param string ...$acceptableFormats
     * @return FormatterInterface
     * @throws NotAcceptableHttpException if proper formatter does not exist
     */
    public function create(string ...$acceptableFormats): FormatterInterface
    {
        foreach ($acceptableFormats as $format) {
            if (true === array_key_exists($format, $this->formatters)) {
                return new $this->formatters[$format];
            }
        }

        throw new NotAcceptableHttpException(
            sprintf('Not acceptable. Please, use one of supported media types: %s.', implode(', ', array_keys($this->formatters)))
        );
    }

    /**
     * @return string[]
     */
    private function parseFormatters(string $file): array
    {
        return Yaml::parseFile($file)['formatters'];
    }
}
