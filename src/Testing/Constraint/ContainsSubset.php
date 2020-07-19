<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

final class ContainsSubset extends Constraint
{
    /** @var mixed */
    private $subset;
    private bool $strict;

    /**
     * @param mixed $subset
     */
    public function __construct($subset, bool $strict = false)
    {
        $this->strict = $strict;
        $this->subset = $subset;
    }

    /**
     * {@inheritdoc}
     * @param mixed $other
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $other = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);
        $patched = array_replace_recursive($other, $this->subset);

        if (true === $this->strict) {
            $result = $other === $patched;
        } else {
            $result = $other == $patched;
        }

        if (true === $returnResult) {
            return $result;
        }

        if (true === $result) {
            return null;
        }

        $failure = new ComparisonFailure(
            $patched,
            $other,
            var_export($patched, true),
            var_export($other, true)
        );

        $this->fail($other, $description, $failure);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'has the subset ' . $this->exporter()->export($this->subset);
    }

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }

    /**
     * @param mixed $other
     * @return mixed[]
     */
    private function toArray($other): array
    {
        if (is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array) $other;
    }
}
