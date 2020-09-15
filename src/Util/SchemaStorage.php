<?php
/**
 * Copyright (c) 2020.
 * @author Paweł Antosiak <contact@pawelantosiak.com>
 */

declare(strict_types=1);

namespace Gorynych\Util;

use Cake\Collection\Collection;
use OpenApi\Annotations\Schema;

final class SchemaStorage
{
    /** @var Collection<int, Schema> */
    private Collection $schemas;

    public function __construct(OAReader $oaReader)
    {
        $this->schemas = new Collection($oaReader->read()->components->schemas);
    }

    /**
     * @return Collection<int, Schema>
     */
    public function getSchemas(): Collection
    {
        return $this->schemas;
    }
}
