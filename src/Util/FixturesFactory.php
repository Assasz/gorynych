<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Gorynych\Testing\EntityMock;

final class FixturesFactory
{
    /**
     * @param string $entityNamespace
     * @param int $count Number of fixtures to create
     * @return mixed[]
     * @throws \InvalidArgumentException
     */
    public function create(string $entityNamespace, int $count = 1): array
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Cannot crate less than 1 fixture.');
        }

        foreach (range(1, $count) as $index) {
            $fixtures[$entityNamespace]["fixture_{$index}"] = (array) EntityMock::create($entityNamespace);
        }

        return $fixtures ?? [];
    }
}
