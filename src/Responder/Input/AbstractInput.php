<?php

namespace Garbetjie\WeChat\Responder\Input;

use DateTime;
use SimpleXMLElement;
use WeChat\Responder\Input;

class AbstractInput implements InputInterface
{
    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $recipient;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $id;

    /**
     * @param SimpleXMLElement $xml
     *
     * @return InputInterface
     */
    static public function create ( SimpleXMLElement $xml )
    {
        // Start parsing the input.
        $type = strtolower( $xml->MsgType );
        $function = 'parseInput_' . ucfirst( $type );
        $method = static::class . '::' . $function;

        if ( method_exists( static::class, $method ) && is_callable( $method ) ) {
            $input = call_user_func( $method, $xml );
        } else {
            // @todo: implement unexpected input handling.
        }

        /* @var AbstractInput $input */
        $input->sender = (string) $xml->FromUserName;
        $input->recipient = (string) $xml->ToUserName;
        $input->created = DateTime::createFromFormat( 'U', (int) $xml->CreateTime );
        $input->id = (int) $xml->MsgId;

        return $input;
    }

    /**
     * @return string
     */
    public function sender ()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function recipient ()
    {
        return $this->recipient;
    }

    /**
     * @return DateTime
     */
    public function created ()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function id ()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function unique ()
    {
        return hash( 'sha1', $this->id() . $this->created()->format( 'U' ) );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Text
     */
    static private function parseInput_Text ( SimpleXMLElement $xml )
    {
        return new Input\Text( $xml, false );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Image
     */
    static private function parseInput_Image ( SimpleXMLElement $xml )
    {
        return new Input\Image( $xml );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Audio
     */
    static private function parseInput_Voice ( SimpleXMLElement $xml )
    {
        return new Input\Audio( $xml );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Video
     */
    static private function parseInput_Shortvideo ( SimpleXMLElement $xml )
    {
        return new Input\Video( $xml, true );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Video
     */
    static private function parseInput_Video ( SimpleXMLElement $xml )
    {
        return new Input\Video( $xml, false );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Location
     */
    static private function parseInput_Location ( SimpleXMLElement $xml )
    {
        return new Input\Location( $xml );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Link
     */
    static private function parseInput_Link ( SimpleXMLElement $xml )
    {
        return new Input\Link( $xml );
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return Event
     */
    static private function parseInput_Event ( SimpleXMLElement $xml )
    {
        return new Input\Event( $xml );
    }
}
