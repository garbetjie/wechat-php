<?php

namespace Garbetjie\WeChat\Messaging\Type;

abstract class AbstractMediaType extends AbstractType
{
    /**
     * @var string
     */
    protected $mediaId = '';

    /**
     * @return string
     */
    public function getMediaId ()
    {
        return $this->mediaId;
    }

    /**
     * @param string $mediaId
     */
    public function __construct ( $mediaId )
    {
        $this->mediaId = $mediaId;
    }
}
