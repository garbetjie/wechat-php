<?php

namespace Garbetjie\WeChat\Auth\Storage;

use WeChat\Auth\AccessToken;

interface StorageInterface
{
    /**
     * Responsible for retrieving the authentication token from which persistent storage is in use.
     *
     * @return AccessToken|void
     */
    public function retrieve ( $hash );

    /**
     * Stores the given token to the persistent storage with the given hash.
     *
     * @param string      $hash
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function store ( $hash, AccessToken $accessToken );

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
    public function hash ( $appId, $secretKey );
}
