<?php
declare(strict_types=1);

namespace Archer\Http\Contract;

use Archer\Http\HeaderCollection;
use Archer\Stream\Contract\Stream;

/**
 * HTTP messages consist of requests from a client to a server and 
 * responses from a server to a client. This interface defines the 
 * methods common to each.
 * 
 * Messages are considered immutable; all methods that might change 
 * state MUST be implemented such that they retain the internal state of 
 * the current message and return an instance that retains the changed 
 * state.
 * 
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
interface Message
{
    /**
     * Body of the message
     * 
     * @var Stream The body as a Stream
     */
    public Stream $body { get; }

    /**
     * HTTP protocol version as a string.
     * 
     * The string MUST contain only the HTTP version number 
     * (e.g., "1.1", "1.0").
     * 
     * @var string HTTP protocol version.
     */
    public string $version { get; }

    /**
     * HTTP message headers as a collection.
     * 
     * @var HeaderCollection Collection of all headers for the message.
     */
    public HeaderCollection $headers { get; }

    /**
     * Returns an instance with the specified message body.
     * 
     * The body MUST be a `Stream` object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that
     * has the new body.
     * 
     * @param Stream $stream Message body
     * @return static
     */
    public function body(Stream $stream): static;

    /**
     * Returns an instance with the specified HTTP protocol version.
     * 
     * The version string MUST contain only the HTTP version number 
     * (e.g., "1.1", "1.0").
     * 
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that
     * has the new protocol version.
     * 
     * @param string $version HTTP protocol version
     * @return static
     */
    public function version(string $version): static;

    /**
     * Returns an instance with the specified HTTP headers.
     * 
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that
     * has the new HTTP headers.
     * 
     * @param HeaderCollection $headers HTTP headers.
     * @return static
     */
    public function headers(HeaderCollection $headers): static;
}