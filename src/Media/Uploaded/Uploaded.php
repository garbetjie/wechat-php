<?php

namespace Garbetjie\WeChatClient\Media\Uploaded;

abstract class Uploaded
{
    /**
     * @var string
     */
    private $mediaID;

    /**
     * @var \DateTime|null
     */
    private $expires;

    /**
     * Uploaded constructor.
     *
     * @param string $mediaID
     */
    public function __construct ($mediaID)
    {
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
    public function getExpiresDate ()
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     *
     * @return static
     */
    public function withExpiresDate (\DateTime $expires)
    {
        $new = clone $this;
        $new->expires = $expires;
        
        return $new;
    }
}
