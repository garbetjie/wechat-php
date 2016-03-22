<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Exception\ApiErrorException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;

class BroadcastService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Service constructor.
     *
     * @param Client $client
     */
    public function __construct (Client $client)
    {
        $this->client = $client;
    }

    /**
     * Sends a broadcast message to the specified users of the OA.
     *
     * @param TypeInterface $message
     * @param array         $users
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     * @throws InvalidArgumentException
     */
    public function users (TypeInterface $message, array $users)
    {
        if (count($users) > 10000) {
            throw new InvalidArgumentException("Cannot send broadcast to more than 10,000 users.");
        }

        $json = (new BroadcastMessageFormatter())->format($message);
        $json['touser'] = array_values($users);

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/send",
            [],
            json_encode($json)
        );
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        return $json['msg_id'];
    }

    /**
     * Send the supplied message to all users that belong to the specified group id.
     *
     * @param TypeInterface $message
     * @param int           $group
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function group (TypeInterface $message, $group)
    {
        $json = (new BroadcastMessageFormatter())->format($message);
        $json['filter']['group_id'] = (int)$group;

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/sendall",
            [],
            json_encode($json)
        );
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        return $json['msg_id'];
    }

    /**
     * Send a preview of the broadcast message to the specified OA user.
     *
     * @param TypeInterface $message
     * @param string        $user
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function preview (TypeInterface $message, $user)
    {
        $json = (new BroadcastMessageFormatter())->format($message);
        $json["touser"] = (string)$user;

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/preview",
            [],
            json_encode($json)
        );
        $this->client->send($request);
    }

    /**
     * Deletes the broadcast with the given message id.
     *
     * @param int $messageId
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function delete ($messageId)
    {
        $json = ["msg_id" => (int)$messageId];

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/delete",
            [],
            json_encode($json)
        );
        $this->client->send($request);
    }

    /**
     * Returns an array containing information about the given broadcast.
     * At the moment, the only information returned is the `sent` status of the broadcast.
     *
     * As more information becomes available through the API, it will be added in the result.
     *
     * @param int $messageId The id of the broadcast message to query.
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws ApiErrorException
     */
    public function query ($messageId)
    {
        $json = ["msg_id" => (int)$messageId];

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/get",
            [],
            json_encode($json)
        );
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        $result = [];
        $result['sent'] = (strtoupper($json['msg_status']) === 'SEND_SUCCESS');

        return $result;
    }
}
