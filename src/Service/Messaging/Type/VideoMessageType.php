<?php

namespace Garbetjie\WeChatClient\Service\Messaging\Type;

use Garbetjie\WeChatClient\Service\Messaging\Type\AbstractMediaMessageType;

class VideoMessageMessageType extends AbstractMediaMessageType
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
