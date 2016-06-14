<?php

namespace Garbetjie\WeChatClient\Authentication\Storage;

use DateTime;
use Garbetjie\WeChatClient\Authentication\Storage\StorageInterface;
use Garbetjie\WeChatClient\Authentication\AccessToken;

class Memcached implements StorageInterface
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Memcached constructor.
     *
     * @param \Memcached $memcached
     * @param string           $prefix
     */
    public function __construct (\Memcached $memcached, $prefix = 'wechatToken:')
    {
        $this->memcached = $memcached;
        $this->prefix = (string)$prefix;
    }

    /**
     * Responsible for retrieving the authentication token from which persistent storage is in use.
     *
     * @return AccessToken|void
     */
    public function retrieve ($hash)
    {
        $cached = $this->memcached->get($this->prefix . $hash);

        if ($this->memcached->getResultCode() === \Memcached::RES_SUCCESS) {
            $json = json_decode($cached, true);

            if (isset($json['token'], $json['expires'])) {
                return new AccessToken($json['token'], DateTime::createFromFormat('U', $json['expires']));
            }
        }
    }

    /**
     * Stores the given token to the persistent storage with the given hash.
     *
     * @param string      $hash
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function store ($hash, AccessToken $accessToken)
    {
        $key = $this->prefix . $hash;
        $contents = json_encode($accessToken);

        $this->memcached->set($key, $contents);
    }

    /**
     * Generates a unique hash for the given application ID and secret key combination.
     *
     * When storing the access token, it will be stored with this hash as the unique identifier.
     *
     * @param string $appId     The application ID.
     * @param string $secretKey The secret key.
     *
     * @return string
     */
    public function hash ($appId, $secretKey)
    {
        return hash('sha256', $appId . $secretKey);
    }
}
