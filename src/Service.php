<?php

namespace Garbetjie\WeChatClient;

abstract class Service
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * GroupsService constructor.
     *
     * @param Client $client
     */
    public function __construct (Client $client)
    {
        $this->client = $client;
    }
}
