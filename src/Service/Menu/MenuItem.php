<?php

namespace Garbetjie\WeChatClient\Service\Menu;

use LengthException;
use InvalidArgumentException;

class MenuItem
{
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
     * Opens the QR scanner, and sends the scanned result to the developer's backend.
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
    protected $items = [];

    /**
     * MenuItem constructor.
     *
     * @param string $title - The title for the menu item.
     * @param string $type  - The type of menu item (one of the WECHAT_MENU_ITEM_* constants).
     * @param array  $args  -  The arguments (if any required) to use for the menu item.
     */
    public function __construct ($title, $type, ...$args)
    {
        $this->title = (string)$title;
        $this->type = $type;

        $methodName = 'handleType' . ucfirst($type);
        if (method_exists($this, $methodName) && is_callable([$this, $methodName])) {
            call_user_func_array([$this, $methodName], $args);
        } else {
            $this->key = substr(sha1($this->title), 0, 16);
        }
    }

    /**
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getType ()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getKey ()
    {
        return $this->key;
    }

    /**
     * Adds a new item to the menu. Ensures the menu item remains immutable.
     *
     * @param MenuItem $item
     *
     * @return MenuItem
     */
    public function withItem (MenuItem $item)
    {
        if (count($this->items) >= 5) {
            throw new LengthException("Maximum of 5 items allowed in a sub-menu.");
        }

        $cloned = clone $this;
        $cloned->items[] = $item;
        
        return $cloned;
    }

    /**
     * Removes the specified item from the menu. Ensures the menu item remains immutable.
     *
     * @param MenuItem $item
     * 
     * @return MenuItem
     */
    public function withoutItem (MenuItem $item)
    {
        $cloned = clone $this;
        
        foreach ($cloned->items as $storedIndex => $storedItem) {
            if ($storedItem === $item) {
                unset($cloned->items[$storedIndex]);
                break;
            }
        }
        
        return $cloned;
    }

    /**
     * @return MenuItem[]
     */
    public function getChildren ()
    {
        return $this->items;
    }

    /**
     * Sets the keyword to use when clicking a menu item.
     *
     * @param string $keyword - The keyword to send when clicking the menu item.
     */
    protected function handleTypeClick ($keyword = '')
    {
        $this->key = (string)$keyword;
    }

    /**
     * Validates the URL that will be visited when clicking the menu item.
     *
     * @param string $url - The URL to visit when clicking the menu item.
     */
    protected function handleTypeView ($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Invalid URL.");
        }

        $this->key = $url;
    }
}
