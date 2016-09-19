<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Command;

class JsonDecodeCommand
{
    /**
     * @var string
     */
    private $json;

    /**
     * @param string $json
     */
    public function __construct(string $json)
    {
        $this->json = $json;
    }

    /**
     * @return string
     */
    public function getJson(): string
    {
        return $this->json;
    }
}
