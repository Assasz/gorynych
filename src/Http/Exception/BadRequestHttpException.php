<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Http\Exception;

use Symfony\Component\HttpFoundation\Response;

class BadRequestHttpException extends HttpException
{
    /**
     * @param string|string[] $message
     */
    public function __construct($message = 'Bad request.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, '', $code, $previous);

        $this->message = $message;
    }
}
