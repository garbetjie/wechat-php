<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

abstract class ResultSet
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var array
     */
    private $items = [];

    /**
     * ResultSet constructor.
     *
     * @param int   $totalCount
     * @param array $items
     */
    public function __construct (array $items, $totalCount, $offset)
    {
        $this->total = (int)$totalCount;
        $this->offset = (int)$offset;
        $this->items = $items;
        
        if ($this->total < 0) {
            $this->total = 0;
        }
        
        if ($this->offset < 0) {
            $this->offset = 0;
        } elseif ($this->offset > $this->total) {
            $this->offset = $this->total;
        }
    }

    /**
     * Returns all the items that have been paginated.
     *
     * @return array
     */
    public function getItems ()
    {
        return $this->items;
    }
}
