<?php

namespace Garbetjie\WeChatClient\Users;

class PaginatedResultSet implements \Iterator
{
    /**
     * @var null|string
     */
    private $next;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $pages;
    
    /**
     * @var array
     */
    private $results = [];

    /**
     * PaginatedResultSet constructor.
     *
     * @param string|null $nextOpenID
     * @param int         $totalResults
     * @param array       $openIDs
     */
    public function __construct ($nextOpenID, $totalResults, array $openIDs = [])
    {
        $this->next = $nextOpenID;
        $this->total = $totalResults;
        $this->results = $openIDs;
        
        $this->pages = ceil($totalResults / 10000);
        if ($this->pages < 1) {
            $this->pages = 1;
        }
    }

    /**
     * @return null|string
     */
    public function getNextOpenID ()
    {
        return $this->next;
    }

    /**
     * Returns the total number of users that are following the official account.
     * 
     * @return int
     */
    public function getTotalCount ()
    {
        return $this->total;
    }

    /**
     * Returns the total number of pages.
     * 
     * @return int
     */
    public function getPageCount ()
    {
        return $this->pages;
    }

    /**
     * @return array
     */
    public function getOpenIDs ()
    {
        return $this->results;
    }

    /**
     * @inheritdoc
     */
    public function current ()
    {
        return current($this->results);
    }

    /**
     * @inheritdoc
     */
    public function next ()
    {
        next($this->results);
    }

    /**
     * @inheritdoc
     */
    public function key ()
    {
        return key($this->results);
    }

    /**
     * @inheritdoc
     */
    public function valid ()
    {
        return $this->key() !== null;
    }

    /**
     * @inheritdoc
     */
    public function rewind ()
    {
        reset($this->results);
    }
}
