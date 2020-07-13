<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Formatter;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSON response formatter according to JSON:API Latest Specification (v1.0)
 *
 * @see https://jsonapi.org/format/#document-top-level
 */
class JsonFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($content, int $statusCode): Response
    {
        $response = new JsonResponse(null, $statusCode);

        if (true === in_array($statusCode, range(400, 599), true)) {
            $response->headers->set('Content-Type', 'application/problem+json');
            $response->setData(['errors' => [$content]]);
        } else {
            $response->setData(['data' => $content]);
        }

        return $response;
    }
}
