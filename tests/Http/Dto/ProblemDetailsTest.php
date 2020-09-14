<?php

declare(strict_types=1);

namespace Http\Dto;

use Gorynych\Http\Dto\ProblemDetails;
use Gorynych\Http\Exception\BadRequestHttpException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ProblemDetailsTest extends TestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testCreatesFromThrowable(\Throwable $throwable, string $expectedDetail, int $expectedStatus): void
    {
        $dto = ProblemDetails::fromThrowable($throwable);

        $this->assertSame($expectedDetail, $dto->detail);
        $this->assertSame($expectedStatus, $dto->status);
    }

    /**
     * @return \Generator<array>
     */
    public function provideCases(): \Generator
    {
        yield 'client http exception' => [
            new BadRequestHttpException('Bad request.'),
            'Bad request.',
            Response::HTTP_BAD_REQUEST,
        ];

        yield 'server exception' => [
            new \InvalidArgumentException(),
            'Internal server error.',
            Response::HTTP_INTERNAL_SERVER_ERROR
        ];
    }
}
