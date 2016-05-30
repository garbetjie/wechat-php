<?php

namespace Garbetjie\WeChatClient\Menu;

use Garbetjie\WeChatClient\Menu\MenuItem;
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
     * Adds a new item to the menu. Ensures the menu remains immutable.
     *
     * @param MenuItem $item
     *
     * @return Menu
     */
    public function withItem (MenuItem $item)
    {
        if (count($this->items) >= 3) {
            throw new LengthException("Maximum of 3 items allowed in a menu.");
        }

        $cloned = clone $this;
        $cloned->items[] = $item;

        return $cloned;
    }

    /**
     * Removes the specified item from the menu.
     *
     * @param MenuItem $item
     * 
     * @return Menu
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
}
