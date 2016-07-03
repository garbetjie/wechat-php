<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

class Video extends Uploaded
{
    /**
     * The media ID of the thumbnail image to use for this video.
     * 
     * @var string
     */
    private $thumbnailID;

    /**
     * @inheritdoc
     */
    public function getType ()
    {
        return 'video';
    }

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
