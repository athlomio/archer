<?php
declare(strict_types=1);

namespace Archer\Http;

use InvalidArgumentException;

use Archer\Http\Contract\Request;
use Archer\Http\Enumeration\Method;

/**
 * Representation of an outgoing, client-side request.
 * 
 * Per the HTTP specification, this class includes properties for 
 * each of the following:
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 * 
 * During construction, implementations attempts to set the Host 
 * header from a provided URI if no Host header is provided.
 * 
 * Requests are considered immutable; all methods that might change 
 * state are implemented in such that they retain the internal state 
 * of the current message and return an instance that contains the 
 * changed state.
 */
final class GenericRequest implements Request
{
    use MessageBehavior;

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
    public protected(set) string $target {
        get {
            if (isset($this->target)) {
                return $this->target;
            }

            $target = $this->uri->path;
            if ($target === "") {
                $target = "/";
            }

            if ($this->uri->query !== "") {
                $target .= "?" . $this->uri->query;
            }

            return $target;
        }
    }

    /**
     * The HTTP method of the request
     * 
     * @var Method
     */
    public protected(set) Method $method;
    
    /**
     * URI instance.
     * 
     * This store an URI instance.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     * @var URI
     */
    public protected(set) URI $uri;

    public function __construct(Method $method, URI $uri) 
    {
        $this->headers = new HeaderCollection($this);
        
        $this->method = $method;
        $this->uri = $uri;

        $this->updateHost();
    }

    /**
     * Return an instance with the specific request-target.
     * 
     * If the request needs a non-origin-form request-target e.g., for 
     * specifying an absolute-form, authority-form, or asterisk-form - 
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     * 
     * This method is implemented in such a way as to retain the 
     * immutability of the message, and return an instance that 
     * has the changed request target.
     * 
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 
     * 
     * @param string $target
     * @return static
     * 
     * @throws InvalidArgumentException for invalid request target.
     */
    public function target(string $target): static
    {
        if (preg_match("%\s%", $target)) {
            throw new InvalidArgumentException("Invalid request target provided; cannot contain whitespace");
        }

        if ($target === $this->target) {
            return $this;
        }

        $clone = clone $this;
        $clone->target = $target;

        return $clone;
    }

    /**
     * Return an instance with the provided HTTP method.
     * 
     * This method is implemented in such a way as to retain the
     * immutability of the message, and return an instance that
     * has the changed request method.
     * 
     * @param Method $method
     * @return static
     */
    public function method(Method $method): static
    {
        if ($method === $this->method) {
            return $this;
        }

        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /**
     * Returns an instance with the provided URI.
     * 
     * This method updates the Host header of the returned request
     * by default if the URI contains a host component. If the URI does
     * not contain a host component, any pre-existing Host header is
     * being carried over to the returned request.
     * 
     * You can opt-in to preserving the original state of the Host 
     * header by setting `$preserve` to `true`. When `$preserve` is set
     * to `true`, this method interacts with the Host header in the
     * following ways:
     * - If the Host header is missing or empty, and the new URI 
     *   contains a host component, this method update the Host
     *   header in the returned request.
     * - If the Host header is missing or empty, and the new URI
     *   does not contain a host component, this method will not update
     *   the header in the returned request.
     * - If a Host header is present and non-empty, this method will not
     *   update the Host header in the returned request.
     * 
     * This method is implemented in such a way as to retain the
     * immutability of the message, and return an instance that has
     * the URI instance.
     * 
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param URI $uri New request URI to use.
     * @param bool $preserve Preserve the original state of the Host
     *      header.
     * 
     * @return static 
     */
    public function uri(URI $uri, bool $preserve = false): static
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $clone = clone $this;
        $clone->uri = $uri;

        if ($preserve) {
            return $clone;
        }

        if ($this->headers->has("host")) {
            return $clone;
        }

        $clone->updateHost();
        return $clone;
    }

    /**
     * Create a default request from globals.
     * 
     * @param array $server $_SERVER super-global.
     * @return static
     */
    public static function fromGlobals(array $server): static
    {
        $method = Method::{$server["REQUEST_METHOD"]};
        $uri = new URI(self::buildUriFromGlobals($server))
            ->user(
                $server["PHP_AUTH_USER"] ?? "", 
                $server["PHP_AUTH_PW"] ?? null
            );

        $version = explode("/", $server["HTTP_X_PROTOCOL_VERSION"] ?? $server["SERVER_PROTOCOL"] ?? "HTTP/1.1")[1] ?? "1.1";
        
        $request = new static($method, $uri)
            ->version($version);

        foreach (getallheaders() as $name => $values) {
            $request = $request->headers->with($name, ...explode(",", $values));
        }

        return $request;
    }

    /**
     * Builds the URI from the $_SERVER super global.
     * 
     * @param array $server $_SERVER super-global.
     * @return string
     */
    protected static function buildUriFromGlobals(array $server): string
    {
        $scheme = self::getSchemeFromGlobals($server);
        $host = $server["HTTP_HOST"] ?? $server["SERVER_NAME"] ?? "";
        
        $port = $server["SERVER_PORT"] ?? null;
        $port = is_numeric($port) ? (int) $port : null;
        
        $uri = $server["REQUEST_URI"] ?? "/";
        
        $result = "{$scheme}://{$host}";
        if ($port !== null) {
            $result .= ":{$port}";
        }

        return "{$result}{$uri}";
    }

    /**
     * Resolves the scheme from the $_SERVER global.
     * 
     * It uses 4 methods to get the scheme:
     * - HTTPS
     * - HTTP_X_FORWARDED_PROTO
     * - REQUEST_SCHEME
     * - HTTP (default)
     * 
     * @param array $server $_SERVER super-global
     * @return string
     */
    protected static function getSchemeFromGlobals(array $server): string
    {
        if (array_key_exists("HTTPS", $server) && $_SERVER["HTTPS"] !== "off") {
            return "https";
        }

        if (array_key_exists("HTTP_X_FORWARDED_PROTO", $server)) {
            return strtolower(trim(explode(",", $server["HTTP_X_FORWARDED_PROTO"])[0]));
        }

        if (array_key_exists("REQUEST_SCHEME", $server)) {
            return strtolower($server["REQUEST_SCHEME"]);
        }

        return "http";
    }

    /**
     * Updates the Host header
     *
     * @see uri()
     * @return void
     */
    protected function updateHost(): void
    {
        $host = $this->uri->host;
        if ($host === "") {
            return;
        }

        if ($this->uri->port !== null) {
            $host .= ":" . $this->uri->port;
        }

        $this->headers->append("host", $host);
    }
}