<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport;

use ApiClients\Tests\Foundation\Transport\TransportAwareTraitDummyResource;
use Phake;
use ApiClients\Foundation\Transport\Client;
use ApiClients\Tests\Foundation\TestCase;

class TransportAwareTraitTest extends TestCase
{
    public function testAccess()
    {
        $resource = new TransportAwareTraitDummyResource();
        $transport = Phake::mock(Client::class);
        $resource->setExtraProperties([
            'transport' => $transport,
        ]);
        $this->assertSame($transport, $resource->wrapper('getTransport'));
    }
}
