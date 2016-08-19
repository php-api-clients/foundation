<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\AppVeyor\Transport;

use ApiClients\Foundation\Transport\Options;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use ApiClients\Foundation\Transport\Client;
use ApiClients\Foundation\Transport\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $loop = LoopFactory::create();
        $client = Factory::create(
            $loop,
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        );
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(LoopInterface::class, $client->getLoop());
        $this->assertSame($loop, $client->getLoop());
    }

    public function testCreateWithoutLoop()
    {
        $client = Factory::create(
            null,
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        );
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(LoopInterface::class, $client->getLoop());
    }
}
