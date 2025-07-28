<?php
declare(strict_types=1);

namespace Archer\Http;

use Archer\Stream\GenericStream;
use Archer\Stream\Contract\Stream;

/**
 * HTTP messages consist of requests from a client to a server and 
 * responses from a server to a client. This trait defines the methods 
 * common to each.
 * 
 * Messages are considered immutable; all methods that might change 
 * state are implemented such that they retain the internal state of 
 * the current message and return an instance that retains the changed 
 * state.
 * 
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
trait MessageBehavior
{
    /**
     * Body of the message
     * 
     * @var Stream The body as a Stream
     */
    public protected(set) Stream $body {
        get {
            if (! isset($body)) {
                $resource = fopen("php://temp", "r+");
                $this->body = new GenericStream($resource);
            }

            return $this->body;
        }
    }

    /**
     * HTTP protocol version as a string.
     * 
     * The string MUST contain only the HTTP version number 
     * (e.g., "1.1", "1.0").
     * 
     * @var string HTTP protocol version.
     */
    public protected(set) string $version = "1.1";

    /**
     * HTTP message headers as a collection.
     * 
     * @var HeaderCollection Collection of all headers for the message.
     */
    public protected(set) HeaderCollection $headers;

    /**
     * Returns an instance with the specified message body.
     * 
     * The body MUST be a `Stream` object.
     *
     * @param Stream $stream Message body
     * @return static
     */
    public function body(Stream $stream): static
    {
        $clone = clone $this;
        $clone->body = $stream;

        return $clone;
    }

    /**
     * Returns an instance with the specified HTTP protocol version.
     * 
     * The version string MUST contain only the HTTP version number 
     * (e.g., "1.1", "1.0").
     * 
     * @param string $version HTTP protocol version
     * @return static
     */
    public function version(string $version): static
    {
        $clone = clone $this;
        $clone->version = $version;

        return $clone;
    }

    /**
     * Returns an instance with the specified HTTP headers.
     * 
     * This method is implemented in such a way as to retain the
     * immutability of the message, and return an instance that
     * has the new HTTP headers.
     * 
     * **WARNING**: This method is intended for internal use only.
     * It exists solely to allow the `HeaderCollection` to communicate 
     * with the `Message`. External user should use the `HeaderCollection`
     * methods instead.
     * 
     * @param HeaderCollection $headers HTTP headers.
     * @return static
     */
    public function headers(HeaderCollection $headers): static
    {
        $clone = clone $this;
        $clone->headers = $headers;

        return $clone;
    }
}