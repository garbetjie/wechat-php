# WeChat PHP client library

## Media management

This library provides a simple way to manage and manipulate media for your OA. By uploading media items, you'll be able
to send these media items to your users.

### Using this library

All functionality provided in the `Media` service is available through the `WeChat\WeChat::media()` method.
 An example showing how to retrieve a group instance is shown below:

        $wechat = new WeChat\WeChat();
        $mediaService = $wechat->media();
        
        // Alternatively:
        $mediaService = new WeChat\Media\Service(new WeChat\Client());
    
Exceptions that are thrown are generally an instance of `WeChat\Media\Exception`.

### Creating media items

There are a number of concrete media types that are available for creation:

* Articles
* Audio items
* Images
* Thumbnails
* Videos

Each media item implements the `TypeInterface` interface. Further explanations on using each media item type is provided
 below:


#### Articles

Articles represent news articles that can be delivered to users. Each news article is composed of three required
 attributes, as well as a number of optional ones. These attributes are documented below:

* **title:** (Required) The title of the news article.
* **content:** (Required) The body of the news article. *(there does seem to be some stripping of HTML characters applied)*.
* **thumbnail:** (Required) The media ID of the thumbnail to use for this article. This means you must have uploaded a thumbnail
 prior to creating this article.
* **author:** The name of the article's author (free text)
* **url:** A URL at which the full version of the news article can be found.
* **summary:** A short summary of the article message.
* **showImage:** Boolean value, indicating whether or not to show the thumbnail image.


    // Upload thumbnail first.
    $thumbnail = new Thumbnail();
    $thumbnail->setPath('path/to/image.jpg');
    $wechat->media()->upload($thumbnail);
     
    // Upload article next.
    $article = new Article();
    $article->addItem([
        'title' => 'Article title',
        'content' => '<p>Article content.</p>',
        'thumbnail' => $thumbnail->getId(),
    ]);
    $wechat->media()->upload($article);
     
    // The uploaded article's media ID is available here:
    $articleMediaID = $article->getId();


#### Images / Audio items / Thumbnails / Video

These media types are pretty much the same, and follow the same upload process. They simply need a path from which the
file will be uploaded:

    $media = new Garbetjie\WeChatClient\Media\Type\Image();
    // -- OR --
    $media = new Garbetjie\WeChatClient\Media\Type\Audio();
    // -- OR --
    $media = new Garbetjie\WeChatClient\Media\Type\Thumbnail();
    // -- OR --
    $media = new Garbetjie\WeChatClient\Media\Type\Video();
     
    $media->setPath('path/to/media/item.extension');
    $mediaID = $wechat->media()->upload($media);
    $mediaID = $media->getId();
