<?php

namespace Garbetjie\WeChatClient\Media;

class Video extends FileMedia
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @inheritDoc
     */
    public function getType ()
    {
        return 'video';
    }

    /**
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDescription ()
    {
        return $this->description;
    }

    /**
     * @param string $title
     *
     * @return static
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
     * @return static
     */
    public function withDescription ($description)
    {
        $new = clone $this;
        $new->description = $description;

        return $new;
    }
}
