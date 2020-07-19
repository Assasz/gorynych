<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Not found.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_NOT_FOUND, $message, $code, $previous);
    }
}
