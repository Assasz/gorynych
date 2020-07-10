<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\TestUtil;

use OpenApi\Analysis;

final class TestAnalysis extends Analysis
{
    /**
     * Just skip validation for testing purpose
     */
    public function validate(): void
    {
    }
}
