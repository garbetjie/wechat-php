<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Messaging\Exception\MessagingException;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Garbetjie\WeChatClient\Messaging\Type\MessageTypeInterface;

class BroadcastMessageService extends Service
{
    /**
     * Sends a broadcast message to the specified users of the OA.
     * 
     * Be careful - you are currently unable to send a broadcast message to more than 10,000 users.
     *
     * @param MessageTypeInterface $message
     * @param array                $userOpenIDs
     *
     * @return int
     * 
     * @throws MessagingException
     */
    public function sendToUsers (MessageTypeInterface $message, array $userOpenIDs)
    {
        $json = (new BroadcastMessageFormatter())->format($message);
        $json['touser'] = array_values($userOpenIDs);

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/send",
            [],
            json_encode($json)
        );
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());

        if (! isset($json->msg_id)) {
            throw new MessagingException("bad response: expected property `msg_id`");
        }

        return $json->msg_id;
    }

    /**
     * Send the supplied message to all users that belong to the specified group id.
     *
     * @param MessageTypeInterface $message
     * @param int                  $groupID
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function sendToGroup (MessageTypeInterface $message, $groupID)
    {
        $json = (new BroadcastMessageFormatter())->format($message);
        $json['filter']['group_id'] = (int)$groupID;

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/sendall",
            [],
            json_encode($json)
        );
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());

        if (! isset($json->msg_id)) {
            throw new MessagingException("bad response: expected property `msg_id`");
        }

        return $json->msg_id;
    }

    /**
     * Send a preview of the broadcast message to the specified OA user.
     *
     * @param MessageTypeInterface $message
     * @param string               $userOpenID
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function sendPreview (MessageTypeInterface $message, $userOpenID)
    {
        $json = (new BroadcastMessageFormatter())->format($message);
        $json["touser"] = (string)$userOpenID;

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
     * @param int $broadcastMessageID
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function deleteMessage ($broadcastMessageID)
    {
        $this->client->send(
            new Request(
                "POST",
                "https://api.weixin.qq.com/cgi-bin/message/mass/delete",
                [],
                json_encode(['msg_id' => (int)$broadcastMessageID])
            )
        );
    }

    /**
     * Returns an array containing information about the given broadcast.
     * At the moment, the only information returned is the `sent` status of the broadcast.
     *
     * As more information becomes available through the API, it will be added in the result.
     *
     * @param int $broadcastMessageID - The ID of the broadcast message to query.
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws APIErrorException
     */
    public function queryStatus ($broadcastMessageID)
    {
        $json = ["msg_id" => (int)$broadcastMessageID];

        $request = new Request(
            "POST",
            "https://api.weixin.qq.com/cgi-bin/message/mass/get",
            [],
            json_encode($json)
        );
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        return [
            'status' => strtolower($json['msg_status']),
        ];
    }
}
