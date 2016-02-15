<?php

namespace Garbetjie\WeChat\Media\Type;

use DateTime;

interface TypeInterface
{
    /**
     * Returns the type of this media item for use in the WeChat API.
     *
     * @return string
     */
    public function getType ();

    /**
     * Sets the id of the media item as stored on WeChat's systems.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId ( $id );

    /**
     * Returns the id of the media from WeChat's systems, or NULL if there is no id for it.
     *
     * @return null|string
     */
    public function getId ();

    /**
     * Sets the date on which this media item was created.
     *
     * @param DateTime $created
     *
     * @return void
     */
    public function setCreated ( DateTime $created );

    /**
     * Returns the date on which the media item was created.
     *
     * @return DateTime
     */
    public function getCreated ();
}
