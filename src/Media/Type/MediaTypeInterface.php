<?php

namespace Garbetjie\WeChatClient\Media\Type;

interface MediaTypeInterface
{
    /**
     * Returns the type of this media item for use in the WeChat API.
     *
     * @return string
     */
    public function getType ();

    /**
     * Returns the ID of the media item, assuming it has been uploaded to the WeChat API. If the item has not been
     * uploaded, this will return null.
     * 
     * @return null|string
     */
    public function getID ();

    /**
     * Returns the path to the media item on the local file system. Returns null if the path has not been supplied.
     * 
     * @return null|string
     */
    public function getPath ();

    /**
     * Returns the date the media item was created, if it has been uploaded. If the media item has not been uploaded,
     * this will return null.
     * 
     * @return null|\DateTime
     */
    public function getUploadDate ();

    /**
     * Set the ID of the media item.
     *
     * This method MUST ensure the media item remains immutable.
     * 
     * @param string $id
     *
     * @return MediaTypeInterface
     */
    public function setID ($id);

    /**
     * Set the path to the media item on the local file system.
     *
     * This method MUST ensure the media item remains immutable.
     *
     * @param string $path
     *
     * @return MediaTypeInterface
     */
    public function setPath ($path);

    /**
     * Set the date of when the media item was uploaded.
     *
     * This method MUST ensure the media item remains immutable.
     *
     * @param \DateTime $uploaded
     *
     * @return MediaTypeInterface
     */
    public function setUploadDate (\DateTime $uploaded);
}
