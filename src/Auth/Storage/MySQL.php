<?php

namespace WeChat\Auth\Storage;

use DateTime;
use PDO;
use WeChat\Auth\AccessToken;

class MySQL extends AbstractDatabaseStorage
{
    /**
     * Responsible for retrieving the authentication token from which persistent storage is in use.
     *
     * @return AccessToken|void
     */
    public function retrieve ( $hash )
    {
        $colHash = $this->columns[ 'hash' ];
        $colExpires = $this->columns[ 'expires' ];
        $colToken = $this->columns[ 'token' ];

        $sql = "SELECT `{$colToken}`, `{$colExpires}` FROM `{$this->table}` WHERE `{$colHash}` = ? LIMIT 1";
        $st = $this->pdo->prepare( $sql );
        $st->execute( [ $hash ] );
        $row = $st->fetch( PDO::FETCH_ASSOC );
        $st->closeCursor();

        if ( is_array( $row ) ) {
            return new AccessToken( $row[ $colToken ], DateTime::createFromFormat( 'U', $row[ $colExpires ] ) );
        }
    }

    /**
     * Stores the given token to the persistent storage with the given hash.
     *
     * @param string      $hash
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function store ( $hash, AccessToken $accessToken )
    {
        $colHash = $this->columns[ 'hash' ];
        $colExpires = $this->columns[ 'expires' ];
        $colToken = $this->columns[ 'token' ];

        $sql = "REPLACE INTO `{$this->table}` ( `{$colHash}`, `{$colToken}`, `{$colExpires}` ) VALUES ( ?, ?, ? )";
        $st = $this->pdo->prepare( $sql );
        $st->execute( [ hex2bin( $hash ), (string) $accessToken, $accessToken->expires()->getTimestamp() ] );
    }
}
