<?php

namespace Garbetjie\WeChatClient\Responder;

use Garbetjie\WeChatClient\Messaging\Type\TypeInterface;
use Garbetjie\WeChatClient\Responder\Input\InputInterface;

class Response
{
    /**
     * Boolean value indicating whether or not the response processing should be halted or not.
     *
     * @var bool
     */
    protected $stop = false;

    /**
     * The instance of the message that was sent, if any.
     *
     * @var TypeInterface
     */
    protected $message;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * Response constructor.
     *
     * @param InputInterface $input
     */
    public function __construct ( InputInterface $input )
    {
        $this->input = $input;
    }

    /**
     * Signals that the processing of any further callbacks should be halted.
     *
     */
    public function stop ()
    {
        $this->stop = true;
    }

    /**
     * Returns a boolean value indicating whether or not the callback processing is stopped.
     *
     * @return bool
     */
    public function stopped ()
    {
        return $this->stop;
    }

    /**
     * Outputs the supplied message type. This is done as early as possible to ensure that the response is received,
     * even if there is additional processing to be done.
     *
     * Once a message has been sent, no other responses can be sent.
     *
     * @param TypeInterface $message
     * @return bool
     */
    public function send ( TypeInterface $message )
    {
        // Do nothing if already sent.
        if ( $this->sent() ) {
            return false;
        }

        $this->message = $message;
        $formatter = new ResponseFormatter();
        $reply = $formatter->format( $this->input, $message );

        // Disable gzip compression.
        ini_set( 'zlib.output_compression', false );
        if ( function_exists( 'apache_setenv' ) ) {
            apache_setenv( 'no-gzip', 1 );
        }

        // Output the response.
        header( 'Content-Type: text/xml' );
        header( 'Content-Length: ' . strlen( $reply ) );
        header( 'Connection: close' );
        echo $reply;

        // Ensure it is sent.
        ob_flush();
        flush();
        
        return true;
    }

    /**
     * Returns a boolean value indicating whether or not a response has been sent.
     *
     * @return bool
     */
    public function sent ()
    {
        return $this->message instanceof TypeInterface;
    }
}
