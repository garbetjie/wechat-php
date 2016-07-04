# WeChat PHP SDK

This is a simple PHP library for interacting with the official WeChat APIs. It was created to remove some of the complexity
around interacting with the WeChat API.

# Table of contents

1. [Installation](#1-installation)
2. [Basic usage](#2-basic-usage)
3. [Authentication](#3-authentication)
4. [Groups](#4-groups)
5. [Media](#5-media)
6. [Menus](#6-menus)
7. [QR](#7-qr)

## 1. Installation

You can use [composer](http://getcomposer.org) to install:

    composer require garbetjie/wechat

Requires **PHP 5.6+**.
    
## 2. Basic usage

All available functionality has been split out into separate services. Each of these services require a
`Garbetjie\WeChatClient\Client` instance. This client instance should be passed into the service when instantiating:

    // Create a client instance.
    $client = new Garbetjie\WeChatClient\Client();
     
    // Create a service instance.
    $userService = new Garbetjie\WeChatClient\Users\Service($client);

## 3. Authenticating

Before any interacting with the WeChat API can take place, an access token will be needed. The Authentication service
can be used to acquire an access token:

```php

use Garbetjie\WeChatClient\Client;
use Garbetjie\WeChatClient\Authentication;
 
$appID = 'Your app ID';
$secret = 'Your secret key';
 
try {
    $authService = new Authentication\Service(new Client());
    $client = $authService->authenticate($appID, $secret);
} catch (Authentication\Exception $e) {
    // Handle errors.
}
```
    
Once you have authenticated, all further API calls made will have your access token automatically injected as part of
the request.

### Caching access tokens

There are a limited number of access tokens that can be retrieved for an OA in any given day. For this reason (and for
performance reasons), it is a good idea to cache these access tokens. There are a number of storage mechanisms that are
options available for caching access tokens.

If no storage is specified, a default of storing the access tokens on the file system in the `sys_get_temp_dir()`
directory.

#### File system

    $cacheDirectory = '/tmp';
    $storage = new Garbetjie\WeChatClient\Authentication\Storage\File($cacheDirectory);

#### Memcached

    $memcached = new \Memcached();
    $memcached->addServer('127.0.0.1', 11211);
    $keyPrefix = 'accessToken:';
     
    $storage = new Garbetjie\WeChatClient\Authentication\Storage\Memcached($memcached, $keyPrefix);

#### MySQL

    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mydb', 'root', '');
    $tableName = '_wechat_tokens';
    $columnMapping = [
        'token'   => 'token_column_name',
        'hash'    => 'hash_column_name',
        'expires' => 'expiry_column_name',
    ];
    
    $storage = new Garbetjie\WeChatClient\Authentication\Storage\MySQL($pdo, $tableName, $columnMapping);
    
The MySQL storage adapter can have the table name, as well as the column names customised. This will allow you to ensure
the storage of access tokens fits into your current database structure.

#### Custom interfaces

You can write any custom interfaces you'd like to be able to store access tokens. Any of these custom storage adapters
need to simply implement the `Garbetjie\WeChatClient\Authentication\Storage\StorageInterface` interface.


## 4. Groups

User group management is done through the `Garbetjie\WeChatClient\Groups\Service`. Authentication is required in
order to view and modify groups.

```php
$groupService = new Garbetjie\WeChatClient\Groups\Service($client);
```

When creating, modifying or retrieving groups from the API, instances of `Garbetjie\WeChatClient\Groups\Group` will be
returned.

### Create a group

```php
$group = $groupService->createGroup("Test group");
```

### Modify a group

```php
$changedGroup = $group->withName('New test name');
$groupService->updateGroup($changedGroup);
```
    
### Remove a group
    
```php
$groupService->deleteGroup($group);
```

### Fetch all groups.

```php
$groups = $groupService->getAllGroups();
 
foreach ($groups as $group) {
    echo sprintf(
        "Group #%d with name `%s` has %d user(s)\n",
        $group->getID(),
        $group->getName(),
        $group->getUserCount()
    );
}
```

### Fetch a single group.

In reality, this is a thin wrapper around the `Garbetjie\WeChatClient\Groups\GroupsService::getAllGroups()` method call,
that makes it easier to fetch a single group.

```php
$group = $groupService->getGroup(1);
```


## 5. Media

Media items need to be stored on WeChat's servers before they're able to be sent as messages to users. Both the uploading
and downloading of media items is possible using the `Garbetjie\WeChatClient\Media\Service` service.

### Creating a new instance

```php
$mediaService = new Garbetjie\WeChatClient\Media\Service($client);
```

### Uploading a file

```php
use Garbetjie\WeChatClient\Media\Type;
 
$imageMediaItem = new Type\Image('/path/to/image.jpg');
$uploadedMediaItem = $mediaService->upload($imageMediaItem);
 
// $uploadedMediaItem now has its ID and upload data populated:
$uploadedMediaItem->getMediaID();
$uploadedMediaItem->getExpiresDate();
```

### Downloading a media item.

There are 3 different way of downloading a media item:

1. Into a file (pass the path as the `$into` parameter).

```php
$mediaService->download($uploadedMediaItem->getMediaID(), '/path/to/downloaded.jpg');
```
    
2. Into an already-opened stream (pass a stream into the `$into` parameter).

```php
$fp = fopen('/tmp/downloaded.jpg', 'wb+');
$mediaService->download($uploadedMediaItem->getMediaID(), $fp);
```
        
3. Or into a temporary stream (don't pass anything for the `$into` parameter) created by the `tmpfile()` function.

```php
$fp = $mediaService->download($uploadedMediaItem->getMediaID());
echo sprintf("Image is %d bytes in size", stream_get_length($fp));
fclose($fp);
```

### Available media types
    
#### Thumbnails

Required when uploading a news article. The media ID returned here needs to be used when adding a news article. Supports
**JPG** images only, no larger than 64KB.
 
```php
$thumbnailMediaItem = new Garbetjie\WeChatclient\Media\Type\Thumbnail('/path/to/thumbnail.jpg');
```

#### News

This is used when sending a multi-story news article in a broadcast message. It will need to be uploaded first, and the
resultant message ID will need to be used when sending the broadcast message.

```php
use Garbetjie\WeChatClient\Media\Type;
 
$newsItem = new Type\NewsItem('Article title', 'Content of the article.', $thumbnailMediaItem->getMediaID());
$newsItem = $newsItem->withAuthor('Author name');
$newsItem = $newsItem->withURL('http://example.org');
$newsItem = $newsItem->withSummary('Short summary blurb.');
$newsItem = $newsItem->withImageShowing(true);
 
$news = (new Type\News())->withItem($newsItem);
```
    
#### Audio

Used when sending a snippet of audio to the user. Supported types are AMR and MP3 audio files, no larger than 2MB.

```php
$audioMessage = new \Garbetjie\WeChatClient\Media\Type\Audio('/path/to/item.mp3');
```
    
#### Image

Used to send an image to a user. Supports **BMP**, **PNG**, **JPEG**, **JPG** or **GIF** extensions, no larger than 2MB.

```php
$imageMessage = new \Garbetjie\WeChatClient\Media\Type\Image('/path/to/image.jpg');
```

#### Video

Send a video to a user. Supports **MP4** format, no larger than 10MB in size.

```php
$videoMediaItem = new \Garbetjie\WeChatClient\Media\Type\Video('/path/to/video.mp4');
```


## 6. Menus

Menus that are displayed within an official account can be customised via the WeChat API. The
`Garbetjie\WeChatClient\Menu\MenuService` services enables the modification of this menu:

```php
$menuService = new \Garbetjie\WeChatClient\Menu\Service($client);
```


## 7. QR Codes

QR codes can be generated via the WeChat API. There are two kinds of QR codes that are available: temporary codes, and
permanent codes.

Temporary codes expire after a developer-determine time period (maximum of 30 days), whereas permanent codes never
expire. However, an official account is limited to having 100,000 permanent codes active at any given time.

```php
$qrService = new Garbetjie\WeChatClient\QR\Service($client);
```
 
### Creating a temporary QR code
 
When creating a temporary QR code, you are limited to a QR code value of a number, in the range of 1 to 100,000.
If no expiry time is given, the generated QR code will expire after 30 seconds. You can specify an expiry time of up to
2 592 000 seconds (30 days).

```php
use Garbetjie\WeChatClient\QR;
 
$service = new QR\Service($client);
$temporaryCode1 = $service->createTemporaryCode(1000, 3600); // Expires in an hour.
$temporaryCode2 = $service->createTemporaryCode(1001); // Expires in 30 seconds.
$temporaryCode3 = $service->createTemporaryCode(1002, 2592000); // Expires in 30 days.
```

### Creating a permanent QR code

Permanent QR codes are limited to 100,000 of them, and do not have an expiry date. You can use either a numeric value
(in the range of 1 to 100,000), or you can use a string value of up to 64 characters long.

```php
use Garbetjie\WeChatClient\QR;
 
$service = new QR\Service($client);
$permanentCode = $service->createPermanentCode(1000);
// OR
$permanentCode = $service->createPermanentCode('Look at me');
```

# Terminology

## OA

Official Account. This is basically the responder of a client.

## User

The user of WeChat. Also known as a follower, this is the end user that is accessing the OA from their mobile device, desktop application or web interface.

## Callback messages

Immediate responses (in XML) in response to a keyword sent by the follower. If reply within 5 seconds cannot be gauranteed, then an empty response ( like `die()` ) should be returned, and a customer service (push) message should be sent.

## Customer Service ( Push ) messages

Also known as a push message, this is an asynchronous response made by the OA to the user. Push messages can only be made for 48 hours after a user has interacted with the OA.
