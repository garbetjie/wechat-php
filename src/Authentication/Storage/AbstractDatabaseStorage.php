<?php

namespace Garbetjie\WeChatClient\Authentication\Storage;

use Garbetjie\WeChatClient\Authentication\Storage\StorageInterface;
use PDO;

abstract class AbstractDatabaseStorage implements StorageInterface
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var null|string
     */
    protected $table = '_wechat_tokens';

    /**
     * @var array
     */
    protected $columns = [
        'token'   => 'token',
        'hash'    => 'hash',
        'expires' => 'expires',
    ];

    /**
     * AbstractDatabaseStorage constructor.
     *
     * @param PDO   $pdo
     * @param null  $table
     * @param array $columns
     */
    public function __construct (PDO $pdo, $table = null, array $columns = [])
    {
        $this->pdo = $pdo;

        // If only an array is given, then assume the table stays the same, and different columns are given.
        if (is_array($table)) {
            $columns = $table;
            $table = null;
        }

        if ($table) {
            $this->table = $table;
        }

        if ($columns) {
            $this->columns = array_merge($this->columns, $columns);
        }
    }

    /**
     * Generates a unique hash for the given application ID and secret key combination.
     *
     * When storing the access token, it will be stored with this hash as the unique identifier.
     *
     * @param string $appId     The application ID.
     * @param string $secretKey The secret key.
     *
     * @return string
     */
    public function hash ($appId, $secretKey)
    {
        return hash('sha256', $appId . $secretKey);
    }
}
