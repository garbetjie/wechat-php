# WeChat PHP SDK

This is a simple PHP library for interacting with the official WeChat APIs. It was created to remove some of the complexity
around interacting with the WeChat API.

# Table of contents

1. [Installation](#1-installation)
2. [Basic usage](#2-basic-usage)
3. [Authentication](#3-authentication)
4. [Groups](#4-groups)
5. [Media](#5-media)

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
    $userService = new Garbetjie\WeChatClient\Users\UserService($client);

## 3. Authentication

Before any interacting with the WeChat API can take place, an access token will be needed. The Authentication service
can be used to acquire an access token:

    $client = new Garbetjie\WeChatClient\Client();
    $appID = 'Your app ID';
    $secret = 'Your secret key';
     
    try {
        $authService = new Garbetjie\WeChatClient\Authentication\AuthenticationService($client);
        $accessToken = $authService->authenticate($appID, $secret);
        $client->setAccessToken($accessToken);
    } catch (Garbetjie\WeChatClient\Authentication\Exception\AuthenticationException $e) {
        // Handle errors.
    }
    
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
    $storage = new Garbetjie\WeChatClient\Authentication\Storage\FileStorage($cacheDirectory);

#### Memcached

    $memcached = new \Memcached();
    $memcached->addServer('127.0.0.1', 11211);
    $keyPrefix = 'accessToken:';
     
    $storage = new Garbetjie\WeChatClient\Authentication\Storage\MemcachedStorage($memcached, $keyPrefix);

#### MySQL

    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mydb', 'root', '');
    $tableName = '_wechat_tokens';
    $columnMapping = [
        'token'   => 'token_column_name',
        'hash'    => 'hash_column_name',
        'expires' => 'expiry_column_name',
    ];
    
    $storage = new Garbetjie\WeChatClient\Authentication\Storage\MySQLDatabaseStorage($pdo, $tableName, $columnMapping);
    
The MySQL storage adapter can have the table name, as well as the column names customised. This will allow you to ensure
the storage of access tokens fits into your current database structure.

#### Custom interfaces

You can write any custom interfaces you'd like to be able to store access tokens. Any of these custom storage adapters
need to simply implement the `Garbetjie\WeChatClient\Authentication\Storage\StorageInterface` interface.


## 4. Groups

User group management is done through the `Garbetjie\WeChatClient\Groups\GroupsService`. Authentication is required in
order to view and modify groups.

    $groupService = new Garbetjie\WeChatClient\Groups\GroupsService($client);

When creating, modifying or retrieving groups from the API, instances of `Garbetjie\WeChatClient\Groups\Group` will be
returned.

### Create a group

    $group = $groupService->createGroup("Test group");

### Modify a group

    $changedGroup = $group->withName('New test name');
    $groupService->updateGroup($changedGroup);
    
### Remove a group
    
    $groupService->deleteGroup($group);

### Fetch all groups.

    $groups = $groupService->getAllGroups();
    
    foreach ($groups as $group) {
        echo sprintf(
            "Group #%d with name `%s` has %d user(s)\n",
            $group->getID(),
            $group->getName(),
            $group->getUserCount()
        );
    }

### Fetch a single group.

In reality, this is a thin wrapper around the `Garbetjie\WeChatClient\Groups\GroupsService::getAllGroups()` method call,
that makes it easier to fetch a single group.

    $group = $groupService->getGroup(1);


## 5. Media

Media items need to be stored on WeChat's servers before they're able to be sent as messages to users. Both the uploading
and downloading of media items is possible using the `Garbetjie\WeChatClient\Media\MediaService` service.

### Creating a new instance

    $mediaService = new Garbetjie\WeChatClient\Media\MediaService($client);

### Uploading a file

    $imageMediaItem = new Garbetjie\WeChatClient\Media\Type\ImageMediaType('/path/to/image.jpg');
    $uploadedMediaItem = $mediaService->upload($imageMediaItem);
    
    // $uploadedMediaItem now has its ID and upload data populated:
    $uploadedMediaItem->getID();
    $uploadedMediaItem->getUploadDate();

### Downloading a media item.

There are 3 different way of downloading a media item:

1. Into a file (pass the path as the `$into` parameter).

        $mediaService->download($uploadedMediaItem->getID(), '/path/to/downloaded.jpg');
    
2. Into an already-opened stream (pass a stream into the `$into` parameter).

        $fp = fopen('/tmp/downloaded.jpg', 'wb+');
        $mediaService->download($uploadedMediaItem->getID(), $fp);
        
3. Or into a temporary stream (don't pass anything for the `$into` parameter) created by the `tmpfile()` function.

        $fp = $mediaService->download($uploadedMediaItem->getID());
        echo sprintf("Image is %d bytes in size", stream_get_length($fp));
        fclose($fp);

### Available media types
    
#### Thumbnails

Required when uploading a news article. The media ID returned here needs to be used when adding a news article. Supports
**JPG** images only, no larger than 64KB.
 
    $thumbnailMediaItem = new Garbetjie\WeChatclient\Media\Type\ThumbnailMediaType('/path/to/thumbnail.jpg');

#### Article

This is used when sending a multi-story news article in a broadcast message. It will need to be uploaded first, and the
resultant message ID will need to be used when sending the broadcast message.

    $articleMediaItem = (new Garbetjie\WeChatClient\Media\Type\ArticleMediaType())
        ->withItem([
            'title' => 'Article title',
            'content' => '<h2>Article body</h2><p>Content of the item.</p>',
            'thumbnail' => $thumbnailMediaID,
        ]);
    
#### Audio

Used when sending a snippet of audio to the user. Supported types are AMR and MP3 audio files, no larger than 2MB.

    $audioMessage = new Garbetjie\WeChatClient\Media\Type\AudioMediaType('/path/to/item.mp3');
    
#### Image

Used to send an image to a user. Supports **BMP**, **PNG**, **JPEG**, **JPG** or **GIF** extensions, no larger than 2MB.

    $imageMessage = new Garbetjie\WeChatClient\Media\Type\ImageMediaType('/path/to/image.jpg');

#### Video

Send a video to a user. Supports **MP4** format, no larger than 10MB in size.

    $videoMediaItem = new Garbetjie\WeChatClient\Media\Type\VideoMediaType('/path/to/video.mp4');

# Terminology

## OA

Official Account. This is basically the responder of a client.

## User

The user of WeChat. Also known as a follower, this is the end user that is accessing the OA from their mobile device, desktop application or web interface.

## Callback messages

Immediate responses (in XML) in response to a keyword sent by the follower. If reply within 5 seconds cannot be gauranteed, then an empty response ( like `die()` ) should be returned, and a customer service (push) message should be sent.

## Customer Service ( Push ) messages

Also known as a push message, this is an asynchronous response made by the OA to the user. Push messages can only be made for 48 hours after a user has interacted with the OA.
