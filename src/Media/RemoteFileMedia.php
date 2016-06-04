<?php

namespace Garbetjie\WeChatClient\Media;

class RemoteFileMedia
{
    /**
     * @var string
     */
    protected $mediaID;

    /**
     * @var \DateTime|null
     */
    protected $lastModifiedDate;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $type;

    /**
     * RemoteFileMedia constructor.
     *
     * @param string    $type
     * @param string    $id
     */
    public function __construct ($type, $mediaID)
    {
        $this->type = $type;
        $this->mediaID = $mediaID;
    }

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastModifiedDate ()
    {
        return $this->lastModifiedDate;
    }
    
    /**
     * @return string
     */
    public function getType ()
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getURL ()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return RemoteFileMedia
     */
    public function withURL ($url)
    {
        $new = clone $this;
        $new->url = $url;
        
        return $new;
    }

    /**
     * @param \DateTime $lastModifiedDate
     * 
     * @return RemoteFileMedia
     */
    public function withLastModifiedDate (\DateTime $lastModifiedDate)
    {
        $new = clone $this;
        $new->lastModifiedDate = $lastModifiedDate;
        
        return $new;
    }
}
