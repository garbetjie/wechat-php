<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

abstract class Paginated
{
    /**
     * @var string
     */
    private $mediaID;

    /**
     * @var DateTimeImmutable
     */
    private $updated;

    /**
     * Paginated constructor.
     *
     * @param string                $mediaID
     * @param DateTimeInterface|int $updated
     */
    public function __construct ($mediaID, $updated)
    {
        $this->mediaID = $mediaID;
        
        if (is_int($updated) || is_numeric($updated)) {
            $this->updated = new DateTimeImmutable('@' . $updated);
        } elseif ($updated instanceof DateTime) {
            $this->updated = DateTimeImmutable::createFromMutable($updated);
        } else {
            $this->updated = new DateTimeImmutable('@' . $updated->getTimestamp());
        }
    }

    /**
     * @return string
     */
    public function getMediaID ()
    {
        return $this->mediaID;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedDate ()
    {
        return $this->updated;
    }
}
