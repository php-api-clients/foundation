<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Resources;

use WyriHaximus\ApiClient\Annotations\Collection;
use WyriHaximus\ApiClient\Annotations\Nested;
use WyriHaximus\ApiClient\Resource\ResourceInterface;
use WyriHaximus\ApiClient\Transport\Client;

/**
 * @Nested(sub="SubResource")
 * @Collection(subs="SubResource")
 */
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

    /**
     * @var SubResource
     */
    protected $sub;

    /**
     * @var array
     */
    protected $subs;

    public function id() : int
    {
        return $this->id;
    }

    public function slug() : string
    {
        return $this->slug;
    }

    public function sub() : SubResource
    {
        return $this->sub;
    }

    public function subs() : array
    {
        return $this->subs;
    }

    public function refresh()
    {
    }

    public function setTransport(Client $client)
    {
    }
}
