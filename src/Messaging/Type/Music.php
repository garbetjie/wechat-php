<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use InvalidArgumentException;

/**
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $highQualityUrl
 * @property string $thumbnailID
 */
class Music extends AbstractType
{
    /**
     * @var string
     */
    protected $type = 'music';

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $highQualityUrl;

    /**
     * @var string
     */
    public $thumbnailID;

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
        $this->thumbnailID = $thumbnailId;

        // Set title & description.
        $this->title = (string) $title;
        $this->description = (string) $description;
    }
}
