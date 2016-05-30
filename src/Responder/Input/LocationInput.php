<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class LocationInput extends AbstractInput
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

    /**
     * LocationInput constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->coordinates = [(float)$xml->Location_X, (float)$xml->Location_Y];
        $this->scale = (float)$xml->Scale;
        $this->name = (string)$xml->Label;
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
    public function getEmittedEvent ()
    {
        return 'location';
    }
}
