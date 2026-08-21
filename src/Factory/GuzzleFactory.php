<?php

namespace Kronos\Log\Factory;

use GuzzleHttp\Client;

class GuzzleFactory
{
    public function createClient(array $options = []): Client
    {
        return new Client($options);
    }
}
