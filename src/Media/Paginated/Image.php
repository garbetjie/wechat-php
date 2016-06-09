<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class Image extends Paginated
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $name;

    /**
     * Image constructor.
     *
     * @param string            $mediaID
     * @param string            $name
     * @param DateTimeInterface|int $updated
     */
    public function __construct ($mediaID, $name, $updated)
    {
        parent::__construct($mediaID, $updated);

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return $this->name;
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
     * @return Image
     */
    public function withURL ($url)
    {
        $new = clone $this;
        $new->url = $url;
        
        return $new;
    }
}
