<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use InvalidArgumentException;

class Music implements TypeInterface
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $highQualityURL;

    /**
     * @var string
     */
    private $thumbnailID;

    /**
     * @param string $sourceURL
     * @param string $highQualitySourceURL
     * @param string $thumbnailID
     * @param string|null $title
     * @param string|null $description
     */
    public function __construct ($sourceURL, $highQualitySourceURL, $thumbnailID, $title = null, $description = null)
    {
        // Set URL
        if (filter_var($sourceURL, FILTER_VALIDATE_URL) !== false) {
            $this->url = $sourceURL;
        } else {
            throw new InvalidArgumentException('$url is not a valid URL');
        }

        // Set high quality URL
        if (filter_var($highQualitySourceURL, FILTER_VALIDATE_URL) !== false) {
            $this->highQualityURL = $highQualitySourceURL;
        } else {
            throw new InvalidArgumentException('$hqUrl is not a valid URL');
        }

        // Set thumbnail id.
        $this->thumbnailID = $thumbnailID;

        // Set title & description.
        $this->title = $title;
        $this->description = $description;
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
     * @return string
     */
    public function getSourceURL ()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHighQualitySourceURL ()
    {
        return $this->highQualityURL;
    }

    /**
     * @return string
     */
    public function getThumbnailID ()
    {
        return $this->thumbnailID;
    }

    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'music';
    }
}
