<?php
declare(strict_types=1);

namespace Archer\Http;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing a URI
 * 
 * This class is meant to represent URIs according to RFC 3986 and to 
 * provide methods for most common operations. Additional 
 * functionality for working with URIs can be provided on top of the 
 * class or externally. Its primary use is for HTTP requests, but may 
 * also be used in other contexts.
 * 
 * URIs are considered immutable, all methods that might change state 
 * are and MUST be implemented such that they retain the internal state
 * of the current instance and return an instance that contains the 
 * changed state.
 * 
 * Typically the Host header will also be present in the request 
 * message. For server-side requests, the scheme will typically be 
 * discoverable in the server parameters.
 * 
 * @link http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
class URI implements JsonSerializable
{
    /**
     * Absolute http and https URIs require a host per RFC 7230 
     * Section 2.7 but in generic URIs the host can be empty. So for 
     * http(s) URIs we apply this default host when no host is given 
     * yet to form a valid URI.
     * 
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-2.7
     * @var string
     */
    public const string HTTP_DEFAULT_HOST = "localhost";

    /**
     * Default ports for common protocols
     * 
     * @var array
     */
    public const array DEFAULT_PORTS = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    /**
     * Scheme component of the URI.
     * 
     * If no scheme is present, this will contain an empty string.
     * 
     * The value MUST be normalized to lowercase, per RFC 3986 
     * Section 3.1.
     * 
     * The trailing ":" character is not part of the scheme and MUST 
     * NOT be added.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @var string
     */
    public protected(set) string $scheme = "";

    /**
     * User information component of the URI.
     * 
     * If no user information is present, this will contain an empty 
     * string.
     * 
     * If a user is present in the URI, it will store that value; 
     * additionally, if the password is also present, it will be 
     * appended to the user value, with a colon (":") separating the 
     * values.
     * 
     * The trailing "@" character is not part of the user information 
     * and MUST NOT be added.
     * 
     * @var string URI User Information in the "username[:password]" 
     *      format.
     */
    public protected(set) string $user = "";

    /**
     * Host component of the URI
     * 
     * If no host is present, this will be an empty string.
     * 
     * The value is normalized to lowercase, per RFC 3986 Section 3.2.2.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @var string
     */
    public protected(set) string $host = "";

    /**
     * Port component of the URI
     * 
     * If a port is present, and it is non-standard for the current 
     * scheme, it will be stored as an integer. If the port is the 
     * standard port used with the current scheme, this will be null.
     * 
     * If no port is present, and no scheme is present, this will store
     * a null value.
     * 
     * If no port is present but a scheme is present, this will 
     * also be a null value.
     * 
     * @var ?int
     */
    public protected(set) ?int $port = null;

    /**
     * Authority component of the URI.
     * 
     * If no authority information is present, this will store an empty
     * string.
     * 
     * The authority syntax of the URI is: 
     * 
     * <pre>
     * [user-information@]host[:port]
     * </pre>
     * 
     * If the port component is not set or the standard port of the 
     * current scheme, it will not be included.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @var string
     */
    public protected(set) string $authority {
        get { 
            $result = $this->host;
            if ($this->user !== "") {
                $result = "{$this->user}@{$result}";
            }

            if ($this->port !== null) {
                $result .= ":{$this->port}";
            }

            return $result;
        }
    }

    /**
     * Path component of the URI
     * 
     * The path can either be empty or absolute (starting with a slash)
     * or rootless (not starting with a slash). The implementation 
     * supports all three syntaxes.
     * 
     * Normally, the empty path "" and absolute path "/" are considered
     * equal as defined in the RFC 7230 Section 2.7.3. This 
     * implementation will automatically do this normalization.
     * 
     * The value stored is percent-encoded.
     * 
     * As an example, if the value should include a slash ("/") not 
     * intended as delimiter between path segment, that value need to 
     * be passed in encoded form (e.g., "%2F") to the instance.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @var string
     */
    public protected(set) string $path = "";

    /**
     * Query string of the URI.
     * 
     * If no query string is present, this will store an empty string.
     * 
     * The leading "?" character is not part of the query and MUST NOT 
     * be added.
     * 
     * The value stored is percent-encoded
     * 
     * As an example, if a value in a key/value pair of the query
     * string should include an ampersand ("&") not intended as a 
     * delimiter between values, that value MUST be passed in encoded 
     * form (e.g., "%26") to the instance.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @var string
     */
    public protected(set) string $query = "";

    /**
     * Fragment component of the URI.
     * 
     * If no fragment is present, this will store an empty string.
     * 
     * The leading "#" character is not part of the fragment and MUST
     * NOT be added.
     * 
     * The value stored is percent-encoded.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @var string
     */
    public protected(set) string $fragment = "";

    /**
     * Return an instance with the specified scheme.
     * 
     * This method retain the tate of the current instance, and return 
     * an instance that contains the specified scheme.
     * 
     * Implementation support the schemes "http" and "https" 
     * case-insensitively and also supports other commonly used schemes.
     * 
     * An empty scheme is equivalent to removing the scheme.
     * 
     * @param string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     */
    public function scheme(string $scheme): static
    {
        $clone = clone $this;
        $clone->scheme = strtolower($scheme);
        $clone->handleDefaultPort();
        $clone->validate();

        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     * 
     * This method retain the state of the current instance, and return
     * an instance that contains the specified user information.
     * 
     * Password is optional, but the user information MUST include the 
     * username; an empty string for the username is equivalent to 
     * removing user information.
     * 
     * @param string $username The username to use for authority
     * @param mixed $password The password associated with `$username`.
     * @return static A new instance with the specified user information.
     */
    public function user(string $username, ?string $password = null): static
    {
        $user = $this->processUserInformation($username);
        if ($user !== "" && $password !== null) {
            $user .= ":" . $this->processUserInformation($password);
        }

        $clone = clone $this;
        $clone->user = $user;
        $clone->validate();

        return $clone;
    }

    /**
     * Return an instance with the specified host.
     * 
     * This method retain the state of the current instance, and return
     * an instance that contains the specified host.
     * 
     * An empty host value is equivalent to removing the host.
     * 
     * @param string $host The hostname to use with the new instance.
     * @return static A new instance with the specified host.
     */
    public function host(string $host): static
    {
        $clone = clone $this;
        $clone->host = strtolower($host);
        $clone->validate();

        return $clone;
    }

    /**
     * Return an instance with the specified port.
     * 
     * This method retain the state of the current instance, and return
     * an instance that contains the specified port.
     * 
     * This implementation will raise an exception for ports outside 
     * the established TCP and UDP port ranges.
     * 
     * A null value provided for the port is equivalent to removing 
     * the port information.
     * 
     * @param int|null $port The port use with the new instance; a null
     *      value removes the port information.
     * @return static A new instance with the specified port.
     * 
     * @throws InvalidArgumentException for invalid ports.
     */
    public function port(?int $port): static
    {
        $port = $this->processPort($port);

        $clone = clone $this;
        $clone->port = $port;
        $clone->handleDefaultPort();
        $clone->validate();

        return $clone;
    }

    /**
     * Return an instance with the specified path.
     * 
     * This method retain the state of the current instance, and return
     * an instance that contains the specified path.
     * 
     * The path can either be empty or absolute (starting with a slash)
     * or rootless (not starting with a slash). This implementation 
     * support all three syntaxes.
     * 
     * If the path is intended to be domain-relative rather than path 
     * relative then it must begin with a slash ("/"). Paths not 
     * starting with a slash ("/") are assumed to be relative to some 
     * base path known to the application or consumer.
     * 
     * Users can provide both encoded and decoded path characters. 
     * Implementation ensure the correct encoding as outlined in the 
     * path property.
     * 
     * @param string $path The path to use with the new instance
     * @return static A new instance with the specified path.
     */
    public function path(string $path): static
    {
        $path = $this->processPath($path);

        $clone = clone $this;
        $clone->path = $path;
        $clone->validate();

        return $clone;
    }

    public function query(string $query): static
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * Return the string representation as a URI reference.
     * 
     * Depending on which components of the URI are present, the 
     * resulting string is either a full URI or relative reference 
     * according to RFC 3986, Section 4.1. The method concatenates the 
     * various components of the URI, using the appropriate delimiters:
     * - If a scheme is present, it will be suffixed by ":".
     * - If an authority is present, it will be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are
     *   two cases where the path has to be adjusted to make the URI
     *   reference valid:
     *      - If the path is rootless and an authority is present, the 
     *        path will be prefixed by "/".
     *      - If the path is starting with more than one "/" and no 
     *        authority is present, the starting slashes will be 
     *        reduced to one.
     * - If a query is present, it will be prefixed by "?".
     * - If a fragment is present, it will be prefixed by "#".
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString(): string
    {
        $result = "";
        if ($this->scheme !== "") {
            $result .=  "{$this->scheme}:";
        }

        if ($this->authority !== "" || $this->scheme === "file") {
            $result .= "//{$this->authority}" ;
        }

        $path = $this->path;
        if ($this->authority !== "" && $path !== "" && $path[0] !== "/") {
            $path = "/{$path}";
        }

        $result .= $path;
        if ($this->query !== "") {
            $result .= "?{$this->query}";
        }

        if ($this->fragment !== "") {
            $result .= "#{$this->fragment}";
        }

        return $result;
    }

    /**
     * Serialize the URI to json.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    /**
     * Check if the port component of the URI is the default port for 
     * the current scheme.
     * 
     * @return bool
     */
    protected function isDefaultPort(): bool
    {
        if ($this->port === null) {
            return true;
        }

        if (! array_key_exists($this->scheme, self::DEFAULT_PORTS)) {
            return false;
        }

        return $this->port === self::DEFAULT_PORTS[$this->scheme];
    }

    /**
     * Remove the value for the port component of the URI if it's the 
     * default port for the current scheme.
     * 
     * @return void
     */
    protected function handleDefaultPort(): void
    {
        if ($this->port === null) {
            return;
        }

        if (! $this->isDefaultPort()) {
            return;
        }

        $this->port = null;
    }

    /**
     * Process a component as a user information component.
     * 
     * @param string $component
     * @return void
     */
    protected function processUserInformation(string $component): string
    {
        return preg_replace_callback(
            "/(?:[^%a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=]+|%(?![A-Fa-f0-9]{2}))/",
            fn ($matches) => rawurlencode($matches[0]),
            $component
        );
    }

    /**
     * Process port component of the URI
     * 
     * @param mixed $port
     * @throws \InvalidArgumentException If the port is invalid.
     * 
     * @return int|null
     */
    protected function processPort(?int $port): ?int
    {
        if ($port === null) {
            return null;
        }

        if ($port > 0 && $port < 0xFFFF) {
            return $port;    
        }

        throw new InvalidArgumentException(
            "Invalid port: {$port}. Must be between 0 and 65535"
        );
    }

    protected function processPath(string $path): string
    {
        return preg_replace_callback(
            "/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/]++|%(?![A-Fa-f0-9]{2}))/",
            fn ($matches) => rawurlencode($matches[0]),
            $path
        );
    }

    /**
     * Validate URI components
     * 
     * @return void
     */
    protected function validate(): void
    {
        if ($this->host === "" && in_array($this->scheme, ["http", "https"])) {
            $this->host = self::HTTP_DEFAULT_HOST;
        }

        if ($this->authority !== "") {
            return;
        }

        if (strpos($this->path, "") === 0) {
            // Throw new exception;
        }

        if ($this->scheme !== "") {
            return;
        }

        if (strpos(explode("/", $this->path, 2)[0], ":") !== false) {
            // Throw new exception;
        }
    }
}