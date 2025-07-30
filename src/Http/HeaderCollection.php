<?php
declare(strict_types=1);

namespace Archer\Http;

use Archer\Http\Contract\Message;

/**
 * Collection of HTTP headers.
 * 
 * `HeaderCollection` as part of the `Message` interface are considered 
 * immutable. all methods that might change state are implemented in 
 * such that they retain their internal state and return an instance 
 * that contains the changed state.
 */
final class HeaderCollection
{
    /**
     * All headers stored in the collection.
     * 
     * @var string[][]
     */
    protected array $headers = [];

    public function __construct(
        protected Message $message
    ) {}

    /**
     * Retrieves all header values.
     * 
     * The keys represents the header name as it will be sent over the 
     * wire, and each value is an array of strings associated with the 
     * header.
     * 
     * Since header names are case-insensitive, they are stored in 
     * uppercase for consistency.
     * 
     * @return string[][]
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * Retrieves a message header value by the given case-insensitive 
     * name.
     * 
     * This method returns an array of all the header values of the 
     * given case-insensitive header name.
     * 
     * If the header does not appear in the message, this method return
     * an empty array.
     * 
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the 
     *      given header. If the header does not appear in the message, 
     *      this method will return an empty array.
     */
    public function get(string $name): array
    {
        $name = strtoupper($name);
        return $this->headers[$name] ?? [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single 
     * header.
     * 
     * This method returns all of the header values of the given 
     * case-insensitive header name as a string concatenated together 
     * using a comma.
     * 
     * **NOTE**: Not all header values may be appropriately represented 
     * using comma concatenation. For such headers use the `get()` 
     * method instead and supply your own delimiter when concatenating.
     * 
     * If the header does not appear in the message, this method will 
     * return an empty string.
     * 
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given 
     *      header concatenated together using a comma. If the header 
     *      does not appear in the message, this method will return an 
     *      empty string.
     */
    public function line(string $name): string
    {
        return implode(", ", $this->get($name));
    }

    /**
     * Return an instance of the parent Message with the provided 
     * value(s) replacing the specified header.
     * 
     * Since header names are case-insensitive, they are stored in 
     * uppercase for consistency.
     * 
     * This method is implemented in such a way as to retain the 
     * immutability of the message and collection, and it return an 
     * instance that has the new and updated header values.
     * 
     * @param string $name Case-insensitive header field name.
     * @param string[] $values Header value(s)
     * 
     * @return Message
     */
    public function with(string $name, string ...$values): Message
    {
        $name = strtoupper($name);

        $clone = clone $this;
        $clone->headers[$name] = $values;
        
        if (($clone->headers[$name] ?? []) === ($this->headers[$name] ?? [])) {
            return $this->message;
        }

        return $this->message->headers($clone);
    }

    /**
     * Return an instance of the parent `Message` without the specified 
     * header.
     * 
     * This method is implemented in such a way as to retain the 
     * immutability of the message and collection, and it return an 
     * instance that has the new and updated header values.
     * 
     * @param string $name Case-insensitive header field name.
     * @return Message
     */
    public function without(string $name): Message
    {
        $name = strtoupper($name);

        $clone = clone $this;
        unset($clone->headers[$name]);

        if (($this->headers ?? []) === ($clone->headers ?? [])) {
            return $this->message;
        }

        return $this->message->headers($clone);
    }

    /**
     * Return an instance of the parent `Message` with the specified 
     * header appended with the given value.
     * 
     * Existing values for the specified header will be maintained. 
     * The new value(s) will be appended to the existing list. If the 
     * header did not exist previously, it will be added.
     * 
     * This method is implemented in such a way as to retain the 
     * immutability of the message and collection, and it return an 
     * instance that has the new and updated header values.
     * 
     * @param string $name Case-insensitive header field name.
     * @param string[] $values Header value(s)
     * @return Message
     */
    public function append(string $name, string ...$values): Message
    {
        $name = strtoupper($name);

        $clone = clone $this;
        $clone->headers[$name] = array_merge_recursive($this->headers[$name] ?? [], $values);

        if (($this->headers[$name] ?? []) === ($clone->headers[$name] ?? [])) {
            return $this->message;
        }

        return $this->message->headers($clone);
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     * 
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given 
     *      header name using a case-insensitive string comparison. 
     *      Returns false if no matching header name is found in the 
     *      collection.
     */
    public function has(string $name): bool
    {
        $name = strtoupper($name);
        return array_key_exists($name, $this->headers);
    }

    /**
     * Replace the message with an updated version.
     * 
     * This method is implemented in such a way as to retain the
     * immutability of the message, and return an instance that
     * has the new HTTP headers.
     * 
     * **WARNING**: This method is intended for internal use only.
     * It exists solely to allow the `HeaderCollection` to communicate 
     * with the `Message`. External user should use the `Message`
     * methods instead.
     * 
     * @param Message $message
     * @return void
     */
    public function message(Message $message): void
    {
        $this->message = $message;
    }
}