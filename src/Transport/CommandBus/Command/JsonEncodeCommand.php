<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Command;

class JsonEncodeCommand
{
    /**
     * @var array
     */
    private $json;

    /**
     * @param array $json
     */
    public function __construct(array $json)
    {
        $this->json = $json;
    }

    /**
     * @return array
     */
    public function getJson(): array
    {
        return $this->json;
    }
}
