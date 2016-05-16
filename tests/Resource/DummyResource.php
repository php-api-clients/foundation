<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Resource;

use WyriHaximus\ApiClient\Resource\TransportAwareTrait;
use WyriHaximus\ApiClient\Transport\Client;

class DummyResource
{
    use TransportAwareTrait;

    public function getTransportWrapper(): Client
    {
        return $this->getTransport();
    }
}
