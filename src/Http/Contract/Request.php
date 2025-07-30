<?php
declare(strict_types=1);

namespace Archer\Http\Contract;

use InvalidArgumentException;

use Archer\Http\URI;
use Archer\Http\Enumeration\Method;

/**
 * Representation of an outgoing, client-side request.
 * 
 * Per the HTTP specification, this interface includes properties for 
 * each of the following:
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 * 
 * During construction, implementations MUST attempt to set the Host 
 * header from a provided URI if no Host header is provided.
 * 
 * Requests are considered immutable; all methods that might change 
 * state MUST be implemented in such that they retain the internal 
 * state of the current message and return an instance that contains 
 * the changed state.
 */
interface Request extends Message
{
    /**
     * Message's request target.
     * 
     * Store the message's request-target either as it will appear 
     * (for clients), as it appears at request (for servers), or as it 
     * was specified for the instance (see target()).
     * 
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation 
     * (see target() below).
     * 
     * If no URI is available, and no request-target has been 
     * specifically provided, this must be the string "/".
     * 
     * @var string
     */
    public string $target { get; }

    /**
     * The HTTP method of the request
     * 
     * @var Method
     */
    public Method $method { get; }

    /**
     * URI instance.
     * 
     * This store an URI instance.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     * @var URI
     */
    public URI $uri { get; }
    
    /**
     * Return an instance with the specific request-target.
     * 
     * If the request needs a non-origin-form request-target e.g., for 
     * specifying an absolute-form, authority-form, or asterisk-form - 
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     * 
     * This method MUST be implemented in such a way as to retain the 
     * immutability of the message, and MUST return an instance that 
     * has the changed request target.
     * 
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 
     * 
     * @param string $target
     * @return static
     * 
     * @throws InvalidArgumentException for invalid request target.
     */
    public function target(string $target): static;

    /**
     * Return an instance with the provided HTTP method.
     * 
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that
     * has the changed request method.
     * 
     * @param Method $method
     * @return static
     */
    public function method(Method $method): static;

    /**
     * Returns an instance with the provided URI.
     * 
     * This method MUST update the Host header of the returned request
     * by default if the URI contains a host component. If the URI does
     * not contain a host component, any pre-existing Host header MUST
     * be carried over to the returned request.
     * 
     * You can opt-in to preserving the original state of the Host 
     * header by setting `$preserve` to `true`. When `$preserve` is set
     * to `true`, this method interacts with the Host header in the
     * following ways:
     * - If the Host header is missing or empty, and the new URI 
     *   contains a host component, this method MUST update the Host
     *   header in the returned request.
     * - If the Host header is missing or empty, and the new URI
     *   does not contain a host component, this method MUST NOT update
     *   the header in the returned request.
     * - If a Host header is present and non-empty, this method MUST NOT
     *   update the Host header in the returned request.
     * 
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has
     * the URI instance.
     * 
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param URI $uri New request URI to use.
     * @param bool $preserve Preserve the original state of the Host
     *      header.
     * 
     * @return static 
     */
    public function uri(URI $uri, bool $preserve = false): static;
}