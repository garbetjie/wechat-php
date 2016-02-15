<?php

namespace Garbetjie\WeChat\Media\Type;

use DateTime;
use DomainException;
use InvalidArgumentException;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var null|string
     */
    protected $path;

    /**
     * @var DateTime
     */
    protected $created;

    /**
     * @var null|string
     */
    protected $id;

    /**
     * AbstractMessageType constructor.
     */
    public function __construct ()
    {
        $this->created = DateTime::createFromFormat( 'U', 0 );
    }

    /**
     * @return string
     */
    public function getType ()
    {
        if ( $this->type === null ) {
            throw new DomainException( "Unset type." );
        }

        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getCreated ()
    {
        return clone $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated ( DateTime $created )
    {
        $this->created = $created;
    }

    /**
     * @return null|string
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Sets the media item's media id.
     *
     * @param string $id
     */
    public function setId ( $id )
    {
        $this->id = (string) $id;
    }

    /**
     * @return null|string
     */
    public function getPath ()
    {
        return $this->path;
    }

    /**
     * Sets the path to the file on disk that contains the media item's contents.
     *
     * @param string $path
     */
    public function setPath ( $path )
    {
        if ( is_file( $path ) ) {
            $this->path = $path;
        } else {
            throw new InvalidArgumentException( "Path '{$path}' does not exist." );
        }
    }
}
