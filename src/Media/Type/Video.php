<?php

namespace Garbetjie\WeChatClient\Media\Type;

class Video extends FileMedia
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
