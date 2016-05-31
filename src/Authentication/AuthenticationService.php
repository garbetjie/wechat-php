<?php

namespace Garbetjie\WeChatClient\Authentication;

use Garbetjie\WeChatClient\Authentication\Exception\AuthenticationException;
use Garbetjie\WeChatClient\Authentication\Storage\FileStorage;
use Garbetjie\WeChatClient\Authentication\Storage\StorageInterface;
use Garbetjie\WeChatClient\Service;
use GuzzleHttp\Psr7\Request;

class AuthenticationService extends Service
{
    /**
     * @param string                $appId     The application ID
     * @param string                $secretKey The application's secret key.
     * @param StorageInterface|null $storage   The optional storage interface to use when persisting access tokens.
     *                                         Defaults to storing them in the system's temporary directory (retrieved
     *                                         using `sys_get_temp_dir()`).
     *
     * @return AccessToken
     *
     * @throws AuthenticationException
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
            return $cached;
        }

        $request = new Request(
            'GET',
            "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secretKey}"
        );
        $response = $this->client->send($request);
        $json = json_decode((string)$response->getBody());

        if (isset($json->access_token, $json->expires_in)) {
            $token = new AccessToken(
                $json->access_token,
                \DateTime::createFromFormat('U', time() + $json->expires_in)
            );

            $storage->store($hash, $token);

            return $token;
        }
        
        throw new AuthenticationException("bad response: expected properties `access_token`, `expires_in`");
    }
}
