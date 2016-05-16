<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Resource;

use Phake;
use WyriHaximus\ApiClient\Transport\Client;
use WyriHaximus\Tests\ApiClient\TestCase;

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
