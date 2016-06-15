<?php

namespace Garbetjie\WeChatClient\Messaging;

use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Service as BaseService;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;

class Service extends BaseService
{
    /**
     * Sends the given message to the specified user.
     * 
     * @param TypeInterface $message - The message to be sent.
     * @param string        $openID  - The user's Open ID to send the message to.
     */
    public function sendPushMessageToUser(TypeInterface $message, $openID)
    {
        $this->client->send(
            new Request(
                "POST",
                "https://api.weixin.qq.com/cgi-bin/message/custom/send",
                [],
                json_encode(
                    (new Formatter())->formatMessageForPush($message, $openID)
                )
            )
        );
    }

    /**
     * Sends the given messages as a broadcast to the specified group. Returns the ID of the broadcast message, in order
     * to query the status of the message at a later stage.
     * 
     * @param TypeInterface $message
     * @param int           $groupID
     *
     * @return int
     * @throws Exception
     */
    public function sendBroadcastMessageToGroup (TypeInterface $message, $groupID)
    {
        $json = (new Formatter())->formatMessageForBroadcast($message);
        $json['filter']['group_id'] = (int)$groupID;

        $json = json_decode(
            $this->client->send(
                new Request(
                    "POST",
                    "https://api.weixin.qq.com/cgi-bin/message/mass/sendall",
                    [],
                    json_encode($json)
                )
            )->getBody()
        );

        if (! isset($json->msg_id)) {
            throw new Exception("bad response: expected property `msg_id`");
        }

        return $json->msg_id;
    }
    
    public function sendBroadcastPreviewToUser (TypeInterface $message, $openID)
    {
        $json = (new Formatter())->formatMessageForBroadcast($message);
        $json["touser"] = (string)$openID;

        $this->client->send(
            new Request(
                "POST",
                "https://api.weixin.qq.com/cgi-bin/message/mass/preview",
                [],
                json_encode($json)
            )
        );
    }

    /**
     * Deletes the broadcast with the given message id.
     *
     * @param int $broadcastMessageID
     */
    public function deleteBroadcastMessage ($broadcastMessageID)
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
     * Returns a string that contains the status of the specified broadcast message.
     *
     * @param int $broadcastMessageID - The ID of the broadcast message to query.
     *
     * @return string
     */
    public function queryBroadcastMessageStatus ($broadcastMessageID)
    {
        $json = json_decode(
            $this->client->send(
                new Request(
                    "POST",
                    "https://api.weixin.qq.com/cgi-bin/message/mass/get",
                    [],
                    json_encode(
                        [
                            "msg_id" => (int)$broadcastMessageID,
                        ]
                    )
                )
            )->getBody()
        );

        return strtolower($json->msg_status);
    }

    /**
     * Sends the given message as a broadcast to the specified users. Attempting to send to more than 10,000 users will
     * more than likely result in an error (due to API restrictions).
     *
     * @param TypeInterface $message
     * @param array         $openIDs
     *
     * @return int
     * @throws Exception
     */
    public function sendBroadcastMessageToUsers (TypeInterface $message, array $openIDs)
    {
        $json = (new Formatter())->formatMessageForBroadcast($message);
        $json['touser'] = array_values($openIDs);

        $json = json_decode(
            $this->client->send(
                new Request(
                    "POST",
                    "https://api.weixin.qq.com/cgi-bin/message/mass/send",
                    [],
                    json_encode($json)
                )
            )->getBody()
        );

        if (! isset($json->msg_id)) {
            throw new Exception("bad response: expected property `msg_id`");
        }

        return $json->msg_id;
    }

    /**
     * Sends the templated message with the given template id to to specified user.
     *
     * Returns a message ID that can be used to query the send status of the message at a later stage.
     *
     * @param string $templateID - The long template ID of the template message to send.
     * @param string $openID - The Open ID of the recipient. 
     * @param string $url 
     * @param array  $data - Template data parameters to use.
     * @param array  $options - Additional options to use (currently only supports the `color` option).
     *
     * @return string
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendTemplateMessageToUser ($templateID, $openID, $url, array $data, array $options = [])
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('$url must be a valid URL.');
        }

        $json = json_decode(
            $this->client->send(
                new Request(
                    "POST",
                    "https://api.weixin.qq.com/cgi-bin/message/template/send",
                    [],
                    json_encode(
                        (new Formatter())->formatTemplateMessage(
                            $templateID,
                            $openID,
                            $url,
                            $data,
                            $options
                        )
                    )
                )
            )->getBody()
        );

        if (! isset($json->msgid)) {
            throw new Exception("bad response: expected property `msgid`");
        }

        return $json->msgid;
    }
}
