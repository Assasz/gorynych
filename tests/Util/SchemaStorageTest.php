<?php

declare(strict_types=1);

namespace Gorynych\Tests\Util;

use DG\BypassFinals;
use Gorynych\Util\OAReader;
use Gorynych\Util\SchemaStorage;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;

class SchemaStorageTest extends TestCase
{
    public function testStoresSchemas(): void
    {
        $components = new Components([]);
        $components->schemas = [new Schema([])];
        $openApi = new OpenApi([]);
        $openApi->components = $components;

        BypassFinals::enable();

        $oaReaderMock = $this->createMock(OAReader::class);
        $oaReaderMock->expects($this->once())->method('read')->willReturn($openApi);

        $schemas = (new SchemaStorage($oaReaderMock))->getSchemas();

        $this->assertInstanceOf(Schema::class, $schemas->first());
    }
}
