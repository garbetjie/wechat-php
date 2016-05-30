<?php

namespace Garbetjie\WeChatClient\Authentication\Storage;

use DateTime;
use Garbetjie\WeChatClient\Authentication\Storage\StorageInterface;
use Garbetjie\WeChatClient\Authentication\AccessToken;

class FileStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $base;

    /**
     * FileStorage constructor.
     *
     * @param string $base The base directory in which all access tokens will be stored.
     */
    public function __construct ($base)
    {
        $base = str_replace('\\', '/', $base);
        $base = rtrim((string)$base, '/');

        $this->base = $base;
    }

    /**
     * Responsible for retrieving the authentication token from which persistent storage is in use.
     *
     * @return AccessToken|void
     */
    public function retrieve ($hash)
    {
        $cacheFile = $this->base . "/{$hash}.token";

        // Read the access token from cache.
        if (is_file($cacheFile) && is_readable($cacheFile)) {
            $json = json_decode(file_get_contents($cacheFile), true);

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
        $cacheFile = $this->base . "/{$hash}.token";
        $contents = json_encode($accessToken);

        file_put_contents($cacheFile, $contents);
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
