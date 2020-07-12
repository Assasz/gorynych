<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Gorynych\Http\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Errors representation according to the API Problem spec (RFC 7807)
 *
 * @see https://tools.ietf.org/html/rfc7807
 */
final class ProblemDetails
{
    public string $type;
    public string $title;
    public int $status;
    public string $detail;

    public function __construct(string $type, string $title, int $status, string $detail)
    {
        $this->type = $type;
        $this->title = $title;
        $this->status = $status;
        $this->detail = $detail;
    }

    public static function fromThrowable(\Throwable $throwable): self
    {
        return new self(
            'https://tools.ietf.org/html/rfc2616#section-10',
            'An error occurred',
            $throwable instanceof HttpException ? $throwable->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR,
            $throwable instanceof HttpException ? $throwable->getMessage() : 'Internal server error'
        );
    }
}
