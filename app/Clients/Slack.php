<?php


namespace App\Clients;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Slack
{
    protected $client;
    protected $config;

    public function __construct(Client $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function createChannel($name)
    {
        return $this->post('channels.create', [
            'name' => $name,
            'validate' => false
        ]);
    }

    public function joinChannel($channelId, $userId)
    {
        return $this->post('channels.invite', [
            'channel' => $channelId,
            'user' => $userId
        ]);
    }

    /**
     * @param $endpoint
     * @param $params
     * @throws RequestException
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($endpoint, $params) {
        $params = array_merge_recursive($params, [
            'token' => $this->config['app_token']
        ]);
        return $this->client->post(
            $endpoint,
            ['form_params' => $params]
        );
    }


}