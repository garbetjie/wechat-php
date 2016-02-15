<?php

namespace Garbetjie\WeChat\Menu;

use LengthException;
use InvalidArgumentException;

class Item
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $items = [ ];

    /**
     * Item constructor.
     *
     * @param string $title - The title for the menu item.
     * @param string $type  - The type of menu item (one of the WECHAT_MENU_ITEM_* constants).
     * @param array  $args  -  The arguments (if any required) to use for the menu item.
     */
    public function __construct ( $title, $type, $args = [ ] )
    {
        $this->title = (string) $title;
        $this->type = $type;

        $methodName = 'handleType' . ucfirst( $type );
        if ( method_exists( $this, $methodName ) ) {
            call_user_func_array( [ $this, $methodName ], (array) $args );
        } else {
            $this->key = substr( sha1( $this->title ), 0, 16 );
        }
    }

    /**
     * @return string
     */
    public function title ()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function type ()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function key ()
    {
        return $this->key;
    }

    /**
     * Adds a new item to the menu.
     *
     * @param Item $item
     *
     * @return Item
     */
    public function add ( Item $item )
    {
        if ( count( $this->items ) >= 5 ) {
            throw new LengthException( "Maximum of 5 items allowed in a sub-menu." );
        }

        $this->items[] = $item;

        return $item;
    }

    /**
     * Removes the specified item from the menu.
     *
     * @param Item $item
     */
    public function remove ( Item $item )
    {
        foreach ( $this->items as $storedIndex => $storedItem ) {
            if ( $storedItem === $item ) {
                unset( $this->items[ $storedIndex ] );
                break;
            }
        }
    }

    /**
     * @return Item[]
     */
    public function children ()
    {
        return $this->items;
    }

    /**
     * Sets the keyword to use when clicking a menu item.
     *
     * @param string $keyword - The keyword to send when clicking the menu item.
     */
    protected function handleTypeClick ( $keyword = '' )
    {
        $this->key = (string) $keyword;
    }

    /**
     * Validates the URL that will be visited when clicking the menu item.
     *
     * @param string $url - The URL to visit when clicking the menu item.
     */
    protected function handleTypeView ( $url )
    {
        if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
            throw new InvalidArgumentException( "Invalid URL." );
        }

        $this->key = $url;
    }

    /**
     * Click menu type.
     *
     * This will be the same as the user sending in a specific keyword. The keyword sent in will be whatever the key of
     * the menu item is.
     *
     */
    const KEYWORD = 'click';

    /**
     * URL menu type.
     *
     * Sends the user to whatever the URL is that has been specified.
     *
     */
    const URL = 'view';

    /**
     * Opens the QR scanner,  and sends the scanned result to the developer's backend.
     *
     * No response is expected from the developer's backend.
     *
     */
    const SCAN_AND_SEND = 'scancode_waitmsg';

    /**
     * Opens the QR scanner, and sends the scanned result to the developer's backend.
     *
     * The developer's backend then needs to send a message in response to the scanned result.
     *
     */
    const SCAN_AND_WAIT = 'scancode_push';

    /**
     * Pops up a menu asking the user to either select a photo from his/her photo gallery, or take a new photo with
     * the camera.
     *
     */
    const SELECT_PHOTO = 'pic_photo_or_album';

    /**
     * Opens the system's camera, and takes a new photo.
     *
     */
    const TAKE_PHOTO = 'pic_sysphoto';

    /**
     * Select a social post (only available in China).
     *
     */
    const SELECT_POST = 'pic_weixin';

    /**
     * Opens up the location selector, and allows the user to send a location.
     *
     */
    const LOCATION = 'location_select';
}
