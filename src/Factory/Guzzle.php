<?php

namespace Kronos\Log\Factory;

class Guzzle
{

    /**
     * @param array $options
     * @return \GuzzleHttp\Client
     */
    public function createClient(array $options = [])
    {
        return new \GuzzleHttp\Client($options);
    }
}