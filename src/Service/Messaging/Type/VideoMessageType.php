<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

class VideoMessageType extends AbstractMediaMessageType
{
    /**
     * The media ID of the thumbnail image to use for this video.
     * 
     * @var string
     */
    private $thumbnailID;

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

    /**
     * @return string
     */
    public function getThumbnailID ()
    {
        return $this->thumbnailID;
    }
}
