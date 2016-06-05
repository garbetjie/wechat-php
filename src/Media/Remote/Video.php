<?php

namespace Garbetjie\WeChatClient\Media\Remote;

class Video extends Remote
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;
    
    /**
     * @inheritDocx
     */
    public function getType ()
    {
        return 'video';
    }

    /**
     * @return null|string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @return null|string
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @param string $title
     *
     * @return Video
     */
    public function withTitle ($title)
    {
        $new = clone $this;
        $new->title = $title;
        
        return $new;
    }

    /**
     * @param string $description
     *
     * @return Video
     */
    public function withDescription ($description)
    {
        $new = clone $this;
        $new->description = $description;
        
        return $new;
    }
}
