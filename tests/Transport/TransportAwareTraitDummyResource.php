<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport;

use ApiClients\Foundation\Transport\TransportAwareTrait;
use ApiClients\Tests\Foundation\Hydrator\DummyResource;

class TransportAwareTraitDummyResource extends DummyResource
{
    use TransportAwareTrait;
}
