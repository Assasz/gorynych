<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Formatter;

use Symfony\Component\HttpFoundation\Response;

interface FormatterInterface
{
    /**
     * Returns formatted HTTP response
     *
     * @param mixed $content
     * @param int $statusCode
     * @return Response
     */
    public function format($content, int $statusCode): Response;
}
