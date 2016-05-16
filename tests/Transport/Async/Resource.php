<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Transport\Async;

use WyriHaximus\ApiClient\Resource\ResourceInterface;
use WyriHaximus\ApiClient\Transport\Client;

class Resource implements ResourceInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $slug;

    public function id() : int
    {
        return $this->id;
    }

    public function slug() : string
    {
        return $this->slug;
    }

    public function refresh()
    {
    }

    public function setTransport(Client $client)
    {
    }
}
