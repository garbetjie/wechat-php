<?php

namespace WeChat\Responder;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use WeChat\Messaging\Type\Audio;
use WeChat\Messaging\Type\Image;
use WeChat\Messaging\Type\Music;
use WeChat\Messaging\Type\RichMedia;
use WeChat\Messaging\Type\Text;
use WeChat\Messaging\Type\TypeInterface;
use WeChat\Messaging\Type\Video;
use WeChat\Responder\Input\InputInterface;

class ResponseFormatter
{
    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @param InputInterface $input
     * @param TypeInterface  $message
     *
     * @return string
     */
    public function format ( InputInterface $input, TypeInterface $message )
    {
        $this->doc = new DOMDocument();
        $root = $this->doc->createElement( 'xml' );
        $this->doc->appendChild( $root );

        $this->fill( $root, [
            'FromUserName' => $input->recipient(),
            'ToUserName'   => $input->sender(),
            'CreateTime'   => time(),
            'MsgType'      => $message->getType(),
        ] );

        $method = 'fill' . ucfirst( $message->getType() ) . 'Message';
        if ( method_exists( $this, $method ) && is_callable( [ $this, $method ] ) ) {
            call_user_func( [ $this, $method ], $root, $message );
        }

        return $this->doc->saveXML( $this->doc->documentElement );
    }

    /**
     * Recursively fills the given DOM element with the supplied values.
     * 
     * @param DOMElement $parent
     * @param array      $params
     */
    protected function fill ( DOMElement $parent, array $params )
    {
        foreach ( $params as $name => $value ) {
            if ( $value instanceof DOMElement ) {
                $element = $value;
            } else if ( is_array( $value ) ) {
                $element = $this->doc->createElement( $name );
                $this->fill( $element, $value );
            } else {
                $element = $this->doc->createElement( $name );
                $element->appendChild( new DOMCdataSection( $value ) );
            }

            $parent->appendChild( $element );
        }
    }

    /**
     * Fill a text message.
     * 
     * @param DOMElement $root
     * @param Text       $message
     */
    protected function fillTextMessage ( DOMElement $root, Text $message )
    {
        $this->fill( $root, [ 'Content' => $message->getContent() ] );
    }

    /**
     * Fill an image message.
     * 
     * @param DOMElement $root
     * @param Image      $message
     */
    protected function fillImageMessage ( DOMElement $root, Image $message )
    {
        $this->fill(
            $root,
            [
                'Image' => [
                    'MediaId' => $message->getMediaId(),
                ],
            ]
        );
    }

    /**
     * Fill an audio message.
     * 
     * @param DOMElement $root
     * @param Audio      $message
     */
    protected function fillVoiceMessage ( DOMElement $root, Audio $message )
    {
        $this->fill( $root,
            [
                'Voice' => [
                    'MediaId' => $message->getMediaId(),
                ],
            ]
        );
    }

    /**
     * Fill a video message.
     * 
     * @param DOMElement $root
     * @param Video      $message
     */
    protected function fillVideoMessage ( DOMElement $root, Video $message )
    {
        $this->fill( $root,
            [
                'Video' => [
                    'MediaId'      => $message->getMediaId(),
                    'ThumbMediaId' => $message->getThumbnailId(),
                ],
            ]
        );
    }

    /**
     * Fill a music message.
     * 
     * @param DOMElement $root
     * @param Music      $message
     */
    protected function fillMusicMessage ( DOMElement $root, Music $message )
    {
        $append = [ ];
        $append[ 'MusicUrl' ] = $message->getUrl();
        $append[ 'HQMusicUrl' ] = $message->getHighQualityUrl();
        $append[ 'ThumbMediaId' ] = $message->getThumbnail();

        if ( $message->getTitle() ) {
            $append[ 'Title' ] = $message->getTitle();
        }

        if ( $message->getDescription() ) {
            $append[ 'Description' ] = $message->getDescription();
        }

        $this->fill( $root, [ 'Music' => $append ] );
    }

    /**
     * Fill a rich media message.
     * 
     * @param DOMElement $root
     * @param RichMedia  $message
     */
    protected function fillNewsMessage ( DOMElement $root, RichMedia $message )
    {
        $articleElement = $this->doc->createElement( 'Articles' );

        foreach ( $message->getItems() as $item ) {
            $itemElement = $this->doc->createElement( 'item' );

            $this->fill( $itemElement, [
                'Title'       => $item[ 'title' ],
                'Description' => $item[ 'description' ],
                'Url'         => $item[ 'url' ],
                'PicUrl'      => $item[ 'image' ],
            ] );

            $articleElement->appendChild( $itemElement );
        }

        $root->appendChild( $articleElement );
        $this->fill( $root, [ 'ArticleCount' => $articleElement->childNodes->length ] );
    }
}
