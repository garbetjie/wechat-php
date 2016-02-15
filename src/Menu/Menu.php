<?php

namespace WeChat\Menu;

use LengthException;

class Menu
{
    /**
     * @var array
     */
    protected $items = [ ];

    /**
     * @return array
     */
    public function items ()
    {
        return $this->items;
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
        if ( count( $this->items ) >= 3 ) {
            throw new LengthException( "Maximum of 3 items allowed in a menu." );
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
}
