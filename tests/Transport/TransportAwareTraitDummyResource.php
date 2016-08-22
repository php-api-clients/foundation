<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport;

use ApiClients\Foundation\Transport\TransportAwareTrait;
use ApiClients\Foundation\Resource\DummyResource;

class TransportAwareTraitDummyResource extends DummyResource
{
    use TransportAwareTrait;
}
