<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Exception;

use Symfony\Component\HttpFoundation\Response;

final class NotAcceptableHttpException extends HttpException
{
    public function __construct(string $message = 'Not acceptable.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_NOT_ACCEPTABLE, $message, $code, $previous);
    }
}
