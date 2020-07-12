<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Formatter;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class JsonFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($content, int $statusCode): Response
    {
        $response = new JsonResponse($content, $statusCode);

        if ($statusCode < 600 && $statusCode > 399) {
            $response->headers->set('Content-Type', 'application/problem+json');
        }

        return $response;
    }
}
