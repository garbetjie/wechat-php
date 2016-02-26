<?php

namespace Garbetjie\WeChatClient\Messaging\Type;

use DomainException;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @return string
     */
    public function getType ()
    {
        if ( $this->type === null ) {
            throw new DomainException( "Unset type." );
        }

        return $this->type;
    }
}
