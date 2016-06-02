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
     * @param \DateTime $lastModifiedDate
     */
    public function __construct ($type, $id, \DateTime $lastModifiedDate)
    {
        $this->type = $type;
        $this->id = $id;
        $this->lastModifiedDate = $lastModifiedDate;
    }

    /**
     * @return string
     */
    public function getID ()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
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
     * @return string
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
}
