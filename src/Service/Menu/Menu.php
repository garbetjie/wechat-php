<?php

namespace Garbetjie\WeChatClient\Service\Menu;

use Garbetjie\WeChatClient\Service\Menu\MenuItem;
use LengthException;

class Menu
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @return array
     */
    public function getItems ()
    {
        return $this->items;
    }

    /**
     * Adds a new item to the menu.
     *
     * @param MenuItem $item
     *
     * @return MenuItem
     */
    public function addItem (MenuItem $item)
    {
        if (count($this->items) >= 3) {
            throw new LengthException("Maximum of 3 items allowed in a menu.");
        }

        $this->items[] = $item;

        return $item;
    }

    /**
     * Removes the specified item from the menu.
     *
     * @param MenuItem $item
     */
    public function removeItem (MenuItem $item)
    {
        foreach ($this->items as $storedIndex => $storedItem) {
            if ($storedItem === $item) {
                unset($this->items[$storedIndex]);
                break;
            }
        }
    }
}
