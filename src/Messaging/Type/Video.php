<?php

namespace Garbetjie\WeChat\Messaging\Type;

class Video extends AbstractMediaType
{
    /**
     * @var string
     */
    protected $thumbnailId = '';

    /**
     * @var string
     */
    protected $type = 'video';

    /**
     * @param string $mediaId
     * @param string $thumbnailId
     */
    public function __construct ( $mediaId, $thumbnailId )
    {
        parent::__construct( $mediaId );

        $this->thumbnailId = $thumbnailId;
    }

    /**
     * @return string
     */
    public function getThumbnailId ()
    {
        return $this->thumbnailId;
    }
}
