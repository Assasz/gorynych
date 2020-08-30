<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Testing\Constraint;

use JsonSchema\Validator;
use PHPUnit\Framework\Constraint\Constraint;

final class MatchesJsonSchema extends Constraint
{
    private object $schema;
    private ?int $checkMode;

    public function __construct(object $schema, ?int $checkMode = null)
    {
        $this->schema = $schema;
        $this->checkMode = $checkMode;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'matches provided JSON Schema';
    }

    /**
     * {@inheritdoc}
     */
    protected function matches($other): bool
    {
        $other = $this->normalizeJson($other);

        $validator = new Validator();
        $validator->validate($other, $this->schema, $this->checkMode);

        return $validator->isValid();
    }

    /**
     * {@inheritdoc}
     */
    protected function additionalFailureDescription($other): string
    {
        $other = $this->normalizeJson($other);

        $validator = new Validator();
        $validator->validate($other, $this->schema, $this->checkMode);

        $errors = array_map(
            static function (array $error): string {
                return ($error['property'] ? $error['property'] . ': ' : '') . $error['message'];
            },
            $validator->getErrors()
        );

        return implode("\n", $errors);
    }

    /**
     * Normalizes a JSON document
     *
     * Specifically, we should ensure that:
     * 1. a JSON object is represented as a PHP object, not as an associative array
     *
     * @param mixed $document
     * @return object|mixed[]
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    private function normalizeJson($document)
    {
        if (true === is_scalar($document) || true === is_object($document)) {
            return $document;
        }

        if (false === is_array($document)) {
            throw new \InvalidArgumentException('Document must be scalar, array or object.');
        }

        $document = json_encode($document);

        if (false === is_string($document)) {
            throw new \UnexpectedValueException('JSON encode failed.');
        }

        $document = json_decode($document);

        if (false === is_array($document) && false === is_object($document)) {
            throw new \UnexpectedValueException('JSON decode failed.');
        }

        return $document;
    }
}
