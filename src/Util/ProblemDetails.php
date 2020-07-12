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

    public static function fromThrowable(\Throwable $throwable): self
    {
        $self = new self();
        $self->type = 'https://tools.ietf.org/html/rfc2616#section-10';
        $self->title = 'An error occurred';
        $self->status = $throwable instanceof HttpException ? $throwable->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $self->detail = $throwable instanceof HttpException ? $throwable->getMessage() : 'Internal server error.';

        return $self;
    }
}
