<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Exception;

use Symfony\Component\HttpFoundation\Response;

final class UnsupportedMediaTypeHttpException extends HttpException
{
    public function __construct(string $message = 'Unsupported media type.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $message, $code, $previous);
    }
}
