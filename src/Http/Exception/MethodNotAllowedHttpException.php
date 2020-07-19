<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Exception;

use Symfony\Component\HttpFoundation\Response;

class MethodNotAllowedHttpException extends HttpException
{
    public function __construct(string $message = 'Method not allowed.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_METHOD_NOT_ALLOWED, $message, $code, $previous);
    }
}
