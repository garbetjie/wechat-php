# WeChat PHP SDK: Authentication

In order to interact with the WeChat API, you will need to authenticate with it and retrieve an access token. Any
subsequent requests you make to the API will need to have this access token included in the request.

## Basic usage

    $wechat = new WeChat\WeChat();
     
    try {
        $wechat->authenticate( 'APPID', 'APPSECRET' );
    } catch ( WeChat\Auth\Exception $e ) {
        // Authentication failed for some reason.
        // The underlying GuzzleException can be retrieved from $e->getPrevious().
    }


## Using the token

Once authentication has been successful, the WeChat PHP SDK will automatically include the access token with any further
requests you make, ensuring you don't need to do it yourself.

Should you need to access the token value, the `$wechat->authenticate()` method conveniently returns the created access
token for you to use. An example of this is shown below:

    $token = $wechat->authenticate( 'APPID', 'APPSECRET' );
    echo "Token value: {$token->value()}\n";
    echo "Expires: {$token->expiry()->format( DATE_RSS )}\n";
    echo "Valid? " . ( $token->valid() ? "Yes" : "No" ) . "\n";


## Caching access tokens

In order to ensure that requests to create access tokens aren't made too often, there are a number of storage options
available to enable the caching of access tokens.

This will ensure that access tokens will only be refreshed when they near expiry.

### File storage

Stores access tokens on the local file system, in the specified directory.

    $storage = new WeChat\Auth\Storage\FileStorage( __DIR__ );

### Memcached storage

Uses the Memcached in-memory caching service to storage access tokens. It requires an existing instance of `Memcached`.


    $memcached = new Memcached();
    $memcached->addServer( '127.0.0.1', 11211 );
     
    $storage = new WeChat\Auth\Storage\MemcachedStorage( $memcached );

### MySQL storage

Uses a MySQL database to store the access tokens. An existing PDO instance is required. The table name, as well as the
columns used can be customised.

    $pdo = new PDO( 'mysql:dbname=wechat;host=localhost' );
    $storage = new WeChat\Auth\Storage\MysqlStorage( $pdo );
    
    // Change the table name (defaults to _wechat_tokens, with (hash, expires, tokens) columns).
    $storage = new WeChat\Auth\Storage\MysqlStorage( $pdo, 'my_table_name' );
    
    // Change the columns used.
    $storage = new WeChat\Auth\Storage\MysqlStorage( $pdo, [
        'hash'    => 'colname1',
        'expires' => 'colname2',
        'token'   => 'colname3',
    ] );
    
    // Change table and columns.
    $storage = new WeChat\Auth\Storage\MysqlStorage( $pdo, 'table_name', [ 'hash' => 'col1', ... ] );

When changing the names of columns, there are three possible columns that can be changed:

1. **`hash`**: The column in which the hash of the APPID and APPSECRET combination should be stored.
2. **`expires`**: This will contain the column in which the token expiry should be stored. The expiry is stored as a UNIX timestmap,
which means it is stored as an unsigned integer.
3. **`token`**: This holds the actual value of the token.


## Configuring database storage

In order to use the database storage mechanisms, you'll need to have a table configured. Sample table definitions for use
with the default settings for database storage are provided below.

### MySQL

    CREATE TABLE `_wechat_tokens` (
        `hash` BINARY(32) NOT NULL PRIMARY KEY,
        `token` VARBINARY(512) NOT NULL,
        `expires` INT UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=INNODB;
    
