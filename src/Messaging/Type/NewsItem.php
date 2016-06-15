<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use InvalidArgumentException;

class NewsItem
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $imageURL;

    /**
     * NewsItem constructor.
     *
     * @param string $title - The display title.
     * @param string $description - The description of the item.
     * @param string [$url] - The destination URL of the news item. This is where the user will be redirected when clicking on it.
     * @param string [$imageURL] - The URL to the image to display for the news item.
     */
    public function __construct ($title, $description, $url = null, $imageURL = null)
    {
        if ($url !== null && filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('invalid URL for `$url`');
        }

        if ($imageURL !== null && filter_var($imageURL, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('invalid URL for `$image`');
        }
        
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->imageURL = $imageURL;
    }

    /**
     * @return string|null
     */
    public function getURL ()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getImageURL ()
    {
        return $this->imageURL;
    }
}
