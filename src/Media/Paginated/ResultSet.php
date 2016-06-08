<?php

namespace Garbetjie\WeChatClient\Media\Paginated;

abstract class ResultSet
{
    /**
     * @var int
     */
    private $total;

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
    public function __construct ($totalCount, array $items)
    {
        $this->total = $totalCount;

        foreach ($items as $item) {
            $this->items[] = $this->expand($item);
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

    /**
     * Expands the given paginated item into its object representation.
     *
     * @param \stdClass $item
     *
     * @return mixed
     */
    abstract protected function expand ($item);
}
