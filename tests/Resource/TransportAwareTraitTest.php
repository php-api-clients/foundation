<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Resource;

use Phake;
use ApiClients\Foundation\Transport\Client;
use ApiClients\Tests\Foundation\TestCase;

class TransportAwareTraitTest extends TestCase
{
    public function testAccess()
    {
        $resource = new DummyResource();
        $transport = Phake::mock(Client::class);
        $resource->setTransport($transport);
        $this->assertSame($transport, $resource->getTransportWrapper());
    }
}
