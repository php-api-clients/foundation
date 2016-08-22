<?php
declare(strict_types=1);

namespace ApiClients\Foundation\Resource;

use ApiClients\Foundation\Transport\Client;

trait TransportAwareTrait
{
    /**
     * @var
     */
    private $transport;

    /**
     * @return Client
     */
    protected function getTransport(): Client
    {
        return $this->transport;
    }

    /**
     * @param Client $transport
     */
    public function transportSetter(Client $transport)
    {
        $this->transport = $transport;
    }
}
