<?php

namespace Garbetjie\WeChatClient\Responder\Exception;

class BadInputException extends \Exception
{
    /**
     * @var string
     */
    private $input;

    /**
     * BadInputException constructor.
     *
     * @param string $input - The bad input that was given.
     */
    public function __construct ($reason, $input)
    {
        $this->input = $input;
        
        parent::__construct($reason);
    }

    /**
     * @return string
     */
    public function getInput ()
    {
        return $this->input;
    }
}
