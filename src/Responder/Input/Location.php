<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Location extends Input
{
    /**
     * @var array
     */
    private $coordinates = [];

    /**
     * @var float
     */
    private $scale;

    /**
     * @var string
     */
    private $name;

    public function __construct ($sender, $recipient, $messageID, $createdDate, array $coordinates, $scale, $label)
    {
        parent::__construct($sender, $recipient, $messageID, $createdDate);
        
        $this->coordinates = $coordinates;
        $this->scale = $scale;
        $this->name = $label;
    }

    /**
     * Co-ordinates as an array of [ $latitude, $longitude ].
     *
     * @return array
     */
    public function getCoordinates ()
    {
        return $this->coordinates;
    }

    /**
     * The latitude of the location.
     *
     * @return float
     */
    public function getLatitude ()
    {
        return $this->coordinates[0];
    }

    /**
     * Longitude of the location.
     *
     * @return mixed
     */
    public function getLongitude ()
    {
        return $this->coordinates[1];
    }

    /**
     * Scale of the location ("zoom level").
     *
     * @return float
     */
    public function getScale ()
    {
        return $this->scale;
    }

    /**
     * Return the name given to the location.
     *
     * @return string
     */
    public function name ()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmittedType ()
    {
        return Type::LOCATION;
    }
}
