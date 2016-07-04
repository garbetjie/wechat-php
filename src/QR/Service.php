<?php

namespace Garbetjie\WeChatClient\QR;

use DateTimeInterface;
use DateTimeImmutable;
use Garbetjie\WeChatClient\QR\Code;
use Garbetjie\WeChatClient\Service as BaseService;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

class Service extends BaseService
{
    /**
     * Creates a temporary QR code that is only valid for the limited duration given. The expiry given
     * cannot be longer than 2 592 000 seconds (30 days), or less than 1 second.
     *
     * Expiry times of less than 1 second will cause and `InvalidArgumentException` to be thrown.
     *
     * @param int                   $value   The value of the QR code.
     * @param int|DateTimeInterface $expires The duration of the QR code, or the `DateTimeInterface` instance at which
     *                                       the code should expire.
     *
     * @return Code\Temporary
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function createTemporaryCode ($value, $expires = 30)
    {
        if ($expires instanceof DateTimeInterface) {
            $expires = $expires->getTimestamp() - time();
        }

        if ($expires > 2592000) {
            $expires = 2592000;
        } elseif ($expires < 1) {
            throw new InvalidArgumentException("code expiry can not be less than 1 second.");
        }

        $args = $this->createCode([
            'expire_seconds' => $expires,
            'action_name'    => 'QR_SCENE',
            'action_info'    => [
                'scene' => [
                    'scene_id' => $value,
                ],
            ],
        ]);

        return new Code\Temporary($args[0], $args[1], $args[2]);
    }

    /**
     * Creates and returns an instance of a new permanent QR code. This code can be used to download the
     * code contents.
     *
     * @param string|int $value
     *
     * @return Code\Permanent
     *
     * @throws Exception
     */
    public function createPermanentCode ($value)
    {
        $str = is_string($value);

        $args = $this->createCode([
            'action_name' => $str ? 'QR_LIMIT_STR_SCENE' : 'QR_LIMIT_SCENE',
            'action_info' => [
                'scene' => [
                    ($str ? 'scene_str' : 'scene_id') => $value,
                ],
            ],
        ]);

        return new Code\Permanent($args[0], $args[1]);
    }

    /**
     * Download the given QR code into the specified file. If no file is supplied, a temporary one will be created using
     * the `tmpfile()` function.
     *
     * @param string $ticket - The QR code to download.
     * @param string $into   - The path or stream in which to save the QR code.
     *
     * @return resource
     *
     * @throws Exception
     */
    public function downloadCode ($ticket, $into = null)
    {
        return $this->client->send(
            new Request(
                'GET',
                "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}"
            ),
            [
                RequestOptions::SINK => $this->createWritableStream($into),
            ]
        )->getBody()->detach();
    }

    /**
     * Method responsible for the actual interaction with the WeChat API, and returns the arguments used for creating
     * a GroupService code.
     *
     * @param array $body
     *
     * @return array
     *
     * @throws Exception
     */
    private function createCode (array $body)
    {
        $json = json_decode(
            $this->client->send(
                new Request(
                    'POST',
                    'https://api.weixin.qq.com/cgi-bin/qrcode/create',
                    [], 
                    json_encode($body)
                )
            )->getBody()
        );

        // Permanent QR code.
        if (in_array($body['action_name'], ['QR_LIMIT_SCENE', 'QR_LIMIT_STR_SCENE'])) {
            if (! isset($json->ticket, $json->url)) {
                throw new Exception("bad response: expected properties `ticket`, `url`");
            }

            return [$json->ticket, $json->url];
        } // Temporary QR code.
        else {
            if (! isset($json->ticket, $json->url, $json->expire_seconds)) {
                throw new Exception("bad response: expected properties `ticket`, `url`, `expire_seconds`");
            }

            return [$json->ticket, $json->url, new DateTimeImmutable('@' . (time() + $json->expire_seconds))];
        }
    }
}
