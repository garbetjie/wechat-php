<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Video extends AbstractMediaType
{
    /**
     * The media ID of the thumbnail image to use for this video.
     * 
     * @var string
     */
    public $thumbnailID;

    /**
     * @var string
     */
    protected $type = 'video';

    /**
     * @param string $mediaId
     * @param string $thumbnailId
     */
    public function __construct ($mediaId, $thumbnailId)
    {
        parent::__construct($mediaId);
        $this->thumbnailID = $thumbnailId;
    }
}
