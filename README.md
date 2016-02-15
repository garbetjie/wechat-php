# WeChat PHP SDK

This is a simple PHP library for interacting with the official WeChat APIs. It was created to remove some of the complexity
around interacting with the WeChat API.

## Installation

You can use [composer](http://getcomposer.org) to install:

    composer require wechatsa/wechatsa

Requires **PHP 5.5+**.
    
## Basic usage

Almost all interaction begins through the main `WeChat\WeChat` class instance:

    $wechat = new WeChat\WeChat();
     
    $user = $wechat->users()->get( 'ID' ); // Retrieve a user profile.
    $groups = $wechat->groups()->all(); // Retrieve all groups.
    $code = $wechar->qr()->temporary( 'value' ); // Create a temporary QR code.

Before interacting with the API, an access token will be required. More information on retrieving an access token can be
viewed in the [authentication readme](./src/Auth/readme.md).

## Further documentation

Further documentation on how to use each component can be viewed in each component's individual readme file. Links to the
relevant README files have been provided below:

 * [Auth](./src/Auth/README.md)
 * [Groups](./src/Groups/README.md)
 * [Media](./src/Media/README.md)
 * [Menu](./src/Menu/README.md)
 * [Messaging](./src/Messaging/README.md)
 * [QR Codes](./src/QR/README.md)
 * [Responders](./src/Responder/README.md)
 * [Users](./src/Users/README.md)


# Terminology

## OA

Official Account. This is basically the responder of a client.

## User

The user of WeChat. Also known as a follower, this is the end user that is accessing the OA from their mobile device, desktop application or web interface.

## Callback messages

Immediate responses (in XML) in response to a keyword sent by the follower. If reply within 5 seconds cannot be gauranteed, then an empty response ( like `die()` ) should be returned, and a customer service (push) message should be sent.

## Customer Service ( Push ) messages

Also known as a push message, this is an asynchronous response made by the OA to the user. Push messages can only be made for 48 hours after a user has interacted with the OA.
