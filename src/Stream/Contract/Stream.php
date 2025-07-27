<?php
declare(strict_types=1);

namespace Archer\Stream\Contract;

use Exception;
use RuntimeException;

interface Stream
{
    /**
     * Whether or not the stream is seekable.
     * @var bool
     */
    public bool $seekable { get; }

    /**
     * Whether or not the stream is readable.
     * @var bool
     */
    public bool $readable { get; }

    /**
     * Whether or not the stream is writable.
     * @var bool
     */
    public bool $writable { get; }

    /**
     * Size of the stream
     * @var int|null
     */
    public ?int $size { get; }

    /**
     * The stream content
     * @var string
     */
    public string $contents { get; }

    /**
     * Seek to the beginning of the stream.
     * 
     * If the stream is not seekable, this method will rase an 
     * exception; otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws RuntimeException on failure
     */
    public function rewind(): void;

    /**
     * Seek to a position in the stream
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be 
     *      calculated based on the seek offset. Valid values are 
     *      identical to the built-in PHP $whence values for `fseek()`.
     *      - SEEK_SET: Set the position equals to offset bytes
     *      - SEEK_CUR: Set the position to current location plus offset
     *      - SEEK_END: Set the position to end-of-stream plus offset.
     * 
     * @throws \RuntimeException on failure
     */
    public function seek(int $offset, int $whence = SEEK_SET): void;

    /**
     * Read data from the stream.
     * 
     * @param int $length Read up to $length bytes from the object and
     *      return them. Fewer than $length bytes may be returned if 
     *      underlying stream call returns fewer bytes.
     * 
     * @return string Returns the data read from the stream, or an empty 
     *      string if no bytes are available.
     * 
     * @throws RuntimeException if an error occurs.
     */
    public function read(int $length): string;

    /**
     * Write data to the stream.
     * 
     * @param string $string The string that is to be written.
     * 
     * @return bool|int Returns the number of bytes written to the 
     *      stream.
     * 
     * @throws RuntimeException on failure.
     */
    public function write(string $string): int;

    /**
     * Returns the current position of the stream read/write pointer.
     * 
     * @return bool|int Position of the stream pointer
     * @throws \RuntimeException on error
     */
    public function tell(): int;

    /**
     * Returns true if the pointer is at the end of the stream.
     * 
     * @return bool
     * @throws RuntimeException on error
     */
    public function eof(): bool;

    /**
     * Separates any underlying resources from the stream.
     * 
     * After the stream has been detached, the stream is in an unusable
     * state.
     * 
     * @return resource|null Underlying PHP stream
     */
    public function detach(): mixed;

    /**
     * Closes the stream and any underlying resources.
     * 
     * @return void
     */
    public function close(): void;

    /**
     * Get stream metadata as an associative array or retrieve a 
     * specific key.
     * 
     * The keys returned are identical to the keys returned from PHP's 
     * stream_get_meta_data() function.
     * 
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string|null $key Specific metadata to retrieve.
     * @return mixed Returns an associative array if no key is provided.
     *      Returns a specific key value if a key is provided and the
     *      value is found, or null if the key is not found.
     */
    public function getMetadata(?string $key = null): mixed;

    /**
     * Reads all data from the stream into a string, from the beginning
     * to the end.
     * 
     * This method MUST attempt to seek to the beginning of the stream
     * before reading data and read the stream until the end is
     * reached.
     * 
     * Warning: this could attempt to load large amount of data into
     * memory.
     * 
     * @return string
     * @throws Exception on error.
     */
    public function __toString(): string;
}