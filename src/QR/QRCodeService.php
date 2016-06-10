<?php

namespace Garbetjie\WeChatClient\QR;

use DateTimeInterface;
use DateTimeImmutable;
use Garbetjie\WeChatClient\QR\Exception\QRCodeException;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

class QRCodeService extends Service
{
    /**
     * Creates a temporary QR code that is only valid for the limited duration given. The expiry given
     * cannot be longer than 1,800 seconds (30 minutes), or less than 1 second.
     *
     * Expiry times of less than 1 second will cause and `InvalidArgumentException` to be thrown.
     *
     * @param int          $value   The value of the QR code.
     * @param int|DateTimeInterface $expires The duration of the QR code, or the `DateTimeInterface` instance at which the
     *                              code should expire.
     *
     * @return TemporaryCode
     *
     * @throws QRCodeException
     * @throws InvalidArgumentException
     */
    public function createTemporary ($value, $expires = 1800)
    {
        if ($expires instanceof DateTimeInterface) {
            $expires = $expires->getTimestamp() - time();
        }

        if ($expires > 1800) {
            $expires = 1800;
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

        return new TemporaryCode($args[0], $args[1], $args[2]);
    }

    /**
     * Creates and returns an instance of a new permanent QR code. This code can be used to download the
     * code contents.
     *
     * @param string|int $value
     *
     * @return PermanentCode
     *
     * @throws QRCodeException
     */
    public function createPermanent ($value)
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

        return new PermanentCode($args[0], $args[1]);
    }

    /**
     * Download the given QR code into the specified file. If no file is supplied, a temporary one will be created using
     * the `tmpfile()` function.
     *
     * @param CodeInterface $code The QR code to download.
     * @param string        $into The path in which to save the QR code.
     *
     * @return resource
     *
     * @throws QRCodeException
     */
    public function download (CodeInterface $code, $into = null)
    {
        if (is_resource($into)) {
            $stream = $into;
        } elseif (is_string($into)) {
            $stream = fopen($into, 'wb');
            if (! $stream) {
                throw new QRCodeException("can't open file `{$into}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        $request = new Request('GET', "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$code->getTicket()}");
        $response = $this->client->send($request, [RequestOptions::SINK => $stream]);
        $stream = $response->getBody()->detach();

        return $stream;
    }

    /**
     * Method responsible for the actual interaction with the WeChat API, and returns the arguments used for creating
     * a GroupService code.
     *
     * @param array $body
     *
     * @return array
     * 
     * @throws QRCodeException
     */
    protected function createCode (array $body)
    {
        $request = new Request('POST', 'https://api.weixin.qq.com/cgi-bin/qrcode/create', [], json_encode($body));
        $response = $this->client->send($request);
        $json = json_decode($response->getBody());

        // Permanent QR code.
        if (in_array($body['action_name'], ['QR_LIMIT_SCENE', 'QR_LIMIT_STR_SCENE'])) {
            if (! isset($json->ticket, $json->url)) {
                throw new QRCodeException("bad response: expected properties `ticket`, `url`");
            }

            return [$json->ticket, $json->url];
        } // Temporary QR code.
        else {
            if (! isset($json->ticket, $json->url, $json->expire_seconds)) {
                throw new QRCodeException("bad response: expected properties `ticket`, `url`, `expire_seconds`");
            }

            return [$json->ticket, $json->url, new DateTimeImmutable('@' . (time() + $json->expire_seconds))];
        }
    }
}
