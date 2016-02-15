<?php

namespace Garbetjie\WeChat\Messaging\Type;

use InvalidArgumentException;

class Music
{
    /**
     * @var string
     */
    protected $type = 'music';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $highQualityUrl;

    /**
     * @var string
     */
    protected $thumbnailId;

    /**
     * @param string $url
     * @param string $hqUrl
     * @param string $thumbnailId
     * @param string $title
     * @param string $description
     */
    public function __construct ( $url, $hqUrl, $thumbnailId, $title = '', $description = '' )
    {
        // Set URL
        if ( filter_var( $url, FILTER_VALIDATE_URL ) !== false ) {
            $this->url = $url;
        } else {
            throw new InvalidArgumentException( '$url is not a valid URL' );
        }

        // Set high quality URL
        if ( filter_var( $hqUrl, FILTER_VALIDATE_URL ) !== false ) {
            $this->highQualityUrl = $hqUrl;
        } else {
            throw new InvalidArgumentException( '$hqUrl is not a valid URL' );
        }

        // Set thumbnail id.
        $this->thumbnailId = $thumbnailId;

        // Set title & description.
        $this->title = (string) $title;
        $this->description = (string) $description;
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
    public function getUrl ()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getThumbnail ()
    {
        return $this->thumbnailId;
    }

    /**
     * @return string
     */
    public function getHighQualityUrl ()
    {
        return $this->highQualityUrl;
    }
}
