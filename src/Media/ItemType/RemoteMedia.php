<?php

namespace Garbetjie\WeChatClient\Media\ItemType;

class RemoteMedia
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var \DateTime
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
     * RemoteMedia constructor.
     *
     * @param string    $type
     * @param string    $id
     */
    public function __construct ($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getID ()
    {
        return $this->id;
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
     * @return RemoteMedia
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
     * @return RemoteMedia
     */
    public function withLastModifiedDate (\DateTime $lastModifiedDate)
    {
        $new = clone $this;
        $new->lastModifiedDate = $lastModifiedDate;
        
        return $new;
    }
}
