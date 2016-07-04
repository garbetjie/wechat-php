<?php

namespace Garbetjie\WeChatClient;

use Garbetjie\WeChatClient\Exception\APIErrorException;
use Garbetjie\WeChatClient\Exception\BadResponseFormatException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Garbetjie\WeChatClient\Authentication\AccessToken;

class Client extends GuzzleClient
{
    /**
     * @var AccessToken|null
     */
    protected $token;

    /**
     * @var bool
     */
    protected $developerMode = false;

    /**
     * {@inheritdoc}
     */
    public function __construct (array $config = [])
    {
        // Enable developer mode.
        if (isset($config['developerMode'])) {
            $this->developerMode = !! $config['developerMode'];
            unset($config['developerMode']);
        }

        if (! isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }

        if (isset($config['wechat.accessToken'])) {
            if ($config['wechat.accessToken'] instanceof AccessToken) {
                $this->token = $config['wechat.accessToken'];
            } else {
                throw new \InvalidArgumentException("`wechat.accessToken` config item must be an instance of `" . AccessToken::class . "`");
            }
        }

        if (isset($config['wechat.developerMode'])) {
            $this->developerMode = !! $config['wechat.developerMode'];
        }

        $this->configureHandlers($config['handler']);

        // Call the parent's constructor.
        parent::__construct($config);
    }
    
    private function configureHandlers (HandlerStack $handlerStack)
    {
        $this->configureResponseHandler($handlerStack);
        $this->configureAuthenticationHandler($handlerStack);
    }
    
    private function configureResponseHandler (HandlerStack $handlerStack)
    {
        $handlerStack->remove('wechatClient:response');
        $handlerStack->push(
            'wechatClient:response',
            function (callable $handler) {
                return function (RequestInterface $request, array $options = []) use ($handler) {
                    return $handler($request, $options)->then(
                        function (ResponseInterface $response) use ($request) {
                            // Non-success page, so we won't attempt to parse.
                            if ($response->getStatusCode() >= 300) {
                                return $response;
                            }

                            // Check if the response should be JSON decoded
                            $parse = ['application/json', 'text/json', 'text/plain'];
                            if (preg_match('#' . implode('|', $parse) . '#', $response->getHeaderLine('Content-Type')) < 1) {
                                return $response;
                            }

                            // Begin parsing JSON body.
                            $body = (string)$response->getBody();
                            $json = json_decode($body);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new BadResponseFormatException(json_last_error_msg(), json_last_error());
                            }

                            if (isset($json->errcode) && $json->errcode != 0) {
                                $message = isset($json->errmsg) ? $json->errmsg : '';
                                $code = $json->errcode;

                                throw new APIErrorException($message, $code, $request, $response);
                            }

                            return $response;
                        }
                    );
                };
            }
        );
    }
    
    private function configureAuthenticationHandler (HandlerStack $handlerStack)
    {
        $token = $this->token;
        
        $handlerStack->remove('wechatClient:authentication');
        $handlerStack->unshift(
            'wechatClient:authentication',
            function (callable $handler) use ($token) {
                return function (RequestInterface $request, array $options = []) use ($handler, $token) {
                    if ($token) {
                        $newUri = Uri::withQueryValue($request->getUri(), 'access_token', $token->value());
                        $request = $request->withUri($newUri);
                    }

                    return $handler($request, $options);
                };
            }
        );
    }
    
    private function configureDeveloperModeHandler (HandlerStack $handlerStack)
    {
        $developerModeEnabled = $this->developerMode;
        
        $handlerStack->remove('wechatClient:developerMode');
        $handlerStack->unshift(
            'wechatClient:developerMode',
            function (callable $handler) use ($developerModeEnabled) {
                return function (RequestInterface $request, array $options = []) use ($handler, $developerModeEnabled) {
                    if ($developerModeEnabled) {
                        $mapping = [
                            'api.weixin.qq.com' => 'api.devcentral.co.za',
                            'api.wechat.com'    => 'api.devcentral.co.za',
                        ];

                        $uri = $request->getUri();

                        if (isset($mapping[$uri->getHost()])) {
                            $uri = $uri->withHost($mapping[$uri->getHost()]);
                        }

                        $request = $request->withUri($uri);
                    }

                    return $handler ($request, $options);
                };
            }
        );
    }

    /**
     * Sets the access token to be used, and returns the cloned client with the token being used.
     * 
     * @param AccessToken $token
     *
     * @return Client
     */
    public function withAccessToken (AccessToken $token)
    {
        $config = $this->getConfig();
        $this->configureHandlers($config['handler']);
        
        $config['wechat.accessToken'] = $token;
        $config['wechat.developerMode'] = $this->developerMode;
        
        return new static($config);
    }

    /**
     * Returns the access token currently being used by the client.
     * 
     * @return AccessToken|null
     */
    public function getAccessToken ()
    {
        return $this->token;
    }
}
