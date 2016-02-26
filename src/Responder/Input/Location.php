<?php

namespace Garbetjie\WeChatClient\Responder\Input;

use SimpleXMLElement;

class Location extends AbstractInput
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
     * Location constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct (SimpleXMLElement $xml)
    {
        $this->coordinates = [ (float) $xml->Location_X, (float) $xml->Location_Y ];
        $this->scale = (float) $xml->Scale;
        $this->name = (string) $xml->Label;
    }

    /**
     * Co-ordinates as an array of [ $latitude, $longitude ].
     * 
     * @return array
     */
    public function coordinates ()
    {
        return $this->coordinates;
    }

    /**
     * The latitude of the location.
     * 
     * @return float
     */
    public function latitude ()
    {
        return $this->coordinates[0];
    }

    /**
     * Longitude of the location.
     * 
     * @return mixed
     */
    public function longitude ()
    {
        return $this->coordinates[1];
    }

    /**
     * Scale of the location ("zoom level").
     * 
     * @return float
     */
    public function scale ()
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
    public function emits ()
    {
        return 'location';
    }
}
