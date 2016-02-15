<?php

namespace WeChat\Responder;

use SimpleXMLElement;
use WeChat\Messaging\Type\TypeInterface;
use WeChat\Responder\Input\AbstractInput;

class Responder
{
    /**
     * @var TypeInterface
     */
    protected $defaultReply;

    /**
     * @var Handler
     */
    public $on;

    /**
     * Responder constructor.
     */
    public function __construct ()
    {
        $this->on = new Handler();
    }

    /**
     * Run the responder, and respond to any incoming requests.
     *
     * @param array  $params The parameters to use in the request. Defaults to $_GET parameters.
     * @param string $input  If given, assumed to be the XML input. If not, the value will be extracted from
     *                       `php://input`.
     */
    public function respond ( array $params = [ ], $input = null )
    {
        // Default the parameters to $_GET if available.
        if ( func_num_args() < 1 && isset( $_GET ) ) {
            $params = $_GET;
        }

        // @todo: verify signatures

        // Handle debugging.
        if ( isset( $params[ 'echostr' ] ) ) {
            header( 'Content-Type: text/plain' );
            echo $params[ 'echostr' ];

            return;
        }

        // Extract input, and create XML object from it.
        try {
            if ( $input === null ) {
                $input = file_get_contents( 'php://input' );
            }

            $xml = new SimpleXMLElement( $input );
        } catch ( \Exception $e ) {
            throw new Exception( "Unable to parse input as XML.", null, $e );
        }

        $input = AbstractInput::create( $xml );
        $response = new Response( $input );
        $this->on->handle( $input, $response );

        // Send the default response if nothing has been sent, and if a default response has been specified.
        if ( ! $response->sent() && $this->defaultReply instanceof TypeInterface ) {
            $response->send( $this->defaultReply );
        }
    }

    /**
     * Set a default response to use if nothing was matched for the input.
     *
     * @param TypeInterface $message
     */
    public function defaultReply ( TypeInterface $message )
    {
        $this->defaultReply = $message;
    }
}
