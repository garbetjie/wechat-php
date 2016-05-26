<?php

namespace Garbetjie\WeChatClient;

use DateTime;
use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use Garbetjie\WeChatClient\Exception\WeChatClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Garbetjie\WeChatClient\Service\Authentication\AccessToken;
use Garbetjie\WeChatClient\Service\Authentication\Storage\FileStorage;
use Garbetjie\WeChatClient\Service\Authentication\Storage\StorageInterface;

class WeChat
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param Client|null $client
     */
    public function __construct (Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\QR\QRCodeService
     */
    public function qr ()
    {
        return new Service\QR\QRCodeService($this->client);
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\Menu\MenuService
     */
    public function menu ()
    {
        return new Service\Menu\MenuService($this->client);
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\Media\MediaService
     */
    public function media ()
    {
        return new Service\Media\MediaService($this->client);
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\Messaging\PushMessageService
     */
    public function messaging ()
    {
        return new Service\Messaging\PushMessageService($this->client);
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\Groups\GroupsService
     */
    public function groups ()
    {
        return new Service\Groups\GroupsService($this->client);
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\Users\UserService
     */
    public function users ()
    {
        return new Service\Users\UserService($this->client);
    }

    /**
     * @return \Garbetjie\WeChatClient\Service\URL\URLService
     */
    public function urls ()
    {
        return new Service\URL\URLService($this->client);
    }

    /**
     * Sets the client to use. Returns the current WeChat instance for chaining.
     *
     * @param Client $client
     *
     * @return $this
     */
    public function setClient (Client $client)
    {
        $this->client = $client;
        
        return $this;
    }

    /**
     * Returns the client used by this WeChat instance.
     * 
     * @return Client
     */
    public function getClient ()
    {
        return $this->client;
    }

    /**
     * @param string                $appId     The application ID
     * @param string                $secretKey The application's secret key.
     * @param StorageInterface|null $storage   The optional storage interface to use when persisting access tokens.
     *                                         Defaults to storing them in the system's temporary directory (retrieved
     *                                         using `sys_get_temp_dir()`).
     *
     * @return AccessToken
     * 
     * @throws WeChatClientException
     * @throws BadResponseFormatException
     */
    public function authenticate ($appId, $secretKey, StorageInterface $storage = null)
    {
        // Default to file storage.
        if (! $storage) {
            $storage = new FileStorage(sys_get_temp_dir());
        }

        $hash = $storage->hash($appId, $secretKey);
        $cached = $storage->retrieve($hash);

        // Cached access token is still valid. Return it.
        if ($cached instanceof AccessToken && $cached->valid()) {
            $this->client->setAccessToken($cached);

            return $cached;
        }

        $request = new Request(
            'GET',
            "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secretKey}"
        );
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody(), true);

        if (isset($json['access_token'], $json['expires_in'])) {
            $token = new AccessToken(
                $json['access_token'],
                DateTime::createFromFormat('U', time() + $json['expires_in'])
            );
            
            $storage->store($hash, $token);
            $this->client->setAccessToken($token);

            return $token;
        }
        
        throw new BadResponseFormatException("unexpected JSON response: " . json_encode($json));
    }

    /**
     * Retrieve a list of IP addresses that are used by WeChat.
     *
     * @return array
     * 
     * @throws GuzzleException
     */
    public function ipList ()
    {
        $request = new Request('GET', 'https://api.weixin.qq.com/cgi-bin/getcallbackip');
        $response = $this->client->send($request);
        $json = json_decode($response->getBody(), true);

        return isset($json['ip_list']) ? $json['ip_list'] : [];
    }
}
