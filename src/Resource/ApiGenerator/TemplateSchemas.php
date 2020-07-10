<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Resource\ApiGenerator;

final class TemplateSchemas
{
    public const ITEM_RESOURCE_SCHEMA = [
        'get' => [
            'operation' => [
                'template' => 'operations/item/get.php.twig',
                'output' => '/src/Ports/Operation/%s/GetOperation.php',
            ],
            'test' => [
                'template' => 'tests/item/get.php.twig',
                'output' => '/tests/Functional/Api/%s/GetTest.php',
            ],
        ],
        'remove' => [
            'operation' => [
                'template' => 'operations/item/remove.php.twig',
                'output' => '/src/Ports/Operation/%s/RemoveOperation.php',
            ],
            'test' => [
                'template' => 'tests/item/remove.php.twig',
                'output' => '/tests/Functional/Api/%s/RemoveTest.php',
            ],
        ],
        'replace' => [
            'operation' => [
                'template' => 'operations/item/replace.php.twig',
                'output' => '/src/Ports/Operation/%s/ReplaceOperation.php',
            ],
            'test' => [
                'template' => 'tests/item/replace.php.twig',
                'output' => '/tests/Functional/Api/%s/ReplaceTest.php',
            ],
        ],
    ];

    public const COLLECTION_RESOURCE_SCHEMA = [
        'get' => [
            'operation' => [
                'template' => 'operations/collection/get.php.twig',
                'output' => '/src/Ports/Operation/%sCollection/GetOperation.php',
            ],
            'test' => [
                'template' => 'tests/collection/get.php.twig',
                'output' => '/tests/Functional/Api/%sCollection/GetTest.php',
            ],
        ],
        'insert' => [
            'operation' => [
                'template' => 'operations/collection/insert.php.twig',
                'output' => '/src/Ports/Operation/%sCollection/InsertOperation.php',
            ],
            'test' => [
                'template' => 'tests/collection/insert.php.twig',
                'output' => '/tests/Functional/Api/%sCollection/InsertTest.php',
            ],
        ],
    ];
}
