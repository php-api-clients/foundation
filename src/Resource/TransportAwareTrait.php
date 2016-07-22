<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Resource;

use WyriHaximus\ApiClient\Transport\Client;

trait TransportAwareTrait
{
    /**
     * @var
     */
    private $transport;

    /**
     * @param Client $transport
     */
    public function setTransport(Client $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return Client
     */
    protected function getTransport(): Client
    {
        return $this->transport;
    }

    public function unsetTransport()
    {
        $this->transport = null;
    }
}
