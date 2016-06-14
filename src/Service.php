<?php

namespace Garbetjie\WeChatClient;

use InvalidArgumentException;

abstract class Service
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Service constructor.
     *
     * @param Client $client
     */
    public function __construct (Client $client)
    {
        $this->client = $client;
    }

    /**
     * Creates a writable stream that downloaded contents can be stored in.
     *
     * @param string|null $filePath
     *
     * @return resource
     */
    protected function createWritableStream ($filePath)
    {
        if (is_resource($filePath)) {
            $stream = $filePath;
        } elseif (is_string($filePath)) {
            $stream = fopen($filePath, 'wb');
            if (! $stream) {
                throw new InvalidArgumentException("Can't open file `{$filePath}` for writing.");
            }
        } else {
            $stream = tmpfile();
        }

        return $stream;
    }

    /**
     * @param string $path
     *
     * @return resource
     */
    protected function createReadableStream ($path)
    {
        if ($path === null) {
            throw new InvalidArgumentException("path not set when uploading media item. cannot upload.");
        }

        $stream = fopen($path, 'rb');
        if (! $stream) {
            throw new InvalidArgumentException("unable to open `{$path}` for reading.");
        }

        return $stream;
    }
}
