<?php
declare(strict_types=1);

namespace Archer\Stream;

use Throwable;
use Exception;
use RuntimeException;
use InvalidArgumentException;

use Archer\Stream\Contract\Stream;

final class GenericStream implements Stream
{
    /** 
     * @var resource 
     */
    protected mixed $stream;

    /**
     * Hash of readable stream types.
     * @var array
     */
    protected const array READABLE_MODES = [
        'r', 'w+', 'r+', 'x+', 'c+', 'rb', 'w+b', 'r+b', 'x+b',
        'c+b', 'rt', 'w+t', 'r+t', 'x+t', 'c+t', 'a+',
    ];

    /**
     * Hash of writable stream types.
     * @var array
     */
    protected const array WRITABLE_MODES = [
        'w', 'w+', 'rw', 'r+', 'x+', 'c+', 'wb', 'w+b', 'r+b',
        'x+b', 'c+b', 'w+t', 'r+t', 'x+t', 'c+t', 'a', 'a+',
    ];

    public protected(set) bool $seekable = false;
    public protected(set) bool $readable = false;
    public protected(set) bool $writable = false;

    public protected(set) ?int $size = null {
        get {
            if ($this->size !== null) {
                return $this->size;
            }

            if (! isset($this->stream)) {
                return null;
            }

            if ($this->uri) {
                clearstatcache(true, $this->uri);
            }

            $stats = fstat($this->stream);
            if (is_array($stats) && array_key_exists("size", $stats)) {
                $this->size = $stats["size"];
                return $this->size;
            }

            return null;
        }
    }

    public protected(set) string $contents {
        get {
            if (! isset($this->stream)) {
                throw new RuntimeException("Stream is detached");
            }

            if (! $this->readable) {
                throw new RuntimeException("Cannot read from non-readable stream");
            }

            $exception = null;
            set_error_handler(static function (int $code, string $message) use (&$exception): bool {
                $exception = new RuntimeException(sprintf("Unable to read stream contents: %s", $message));

                return true;
            });

            try {
                $contents = stream_get_contents($this->stream);
                if ($contents === false) {
                    $exception = new RuntimeException("Unable to read stream contents");
                }
            } catch (Throwable $exception) {
                $exception = new RuntimeException(sprintf("Unable to read stream contents: %s", $exception->getMessage()), 0, $exception);
            }

            restore_error_handler();
            if ($exception) {
                throw $exception;
            }

            return $contents;
        }
    }

    /**
     * Stream uri
     * @var string|null
     */
    protected ?string $uri = null;

    /**
     * Custom metadata for the stream
     * @var array
     */
    protected array $metadata = [];

    /**
     * @param resource $stream Stream resource to wrap.
     * @param int|null $size Size of the stream.
     * @param array $metadata Custom metadata for the stream.
     * 
     * @throws \InvalidArgumentException If the stream is not a stream resource.
     */
    public function __construct(mixed $stream, ?int $size = null, array $metadata = [])
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException("Stream must be a resource");
        }

        $this->metadata = $metadata;
        if ($size !== null) {
            $this->size = $size;
        }

        $this->stream = $stream;
        $metadata = stream_get_meta_data($this->stream);

        $this->uri = $this->getMetadata("uri");
        $this->seekable = $metadata["seekable"];
        $this->readable = in_array($metadata["mode"], self::READABLE_MODES);
        $this->readable = in_array($metadata["mode"], self::WRITABLE_MODES);
    
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {   
        if (! isset($this->stream)) {
            throw new RuntimeException("Stream is detached");
        }

        if (! $this->seekable) {
            throw new RuntimeException("Stream is not seekable");
        }

        if (fseek($this->stream, $offset, $whence) !== -1) {
            return;
        }

        throw new RuntimeException("Unable to seek to stream position {$offset} with whence " . var_export($whence, true));
    }

    public function read(int $length): string
    {
        if (! isset($this->stream)) {
            throw new RuntimeException("Stream is detached");
        }

        if (! $this->readable) {
            throw new RuntimeException("Cannot read from non-readable stream");
        }

        if ($length < 0) {
            throw new RuntimeException("Length parameter cannot be negative");
        }

        if ($length === 0) {
            return "";
        }

        try {
            $string = fread($this->stream, $length);
        } catch (Exception $exception) {
            throw new RuntimeException("Unable to read from stream", previous: $exception);
        }

        if ($string === false) {
            throw new RuntimeException("Unable to read from stream");
        }

        return $string;
    }
    
    public function write(string $string): int
    {
        if (! isset($this->stream)) {
            throw new RuntimeException("Stream is detached");
        }

        if (! $this->writable) {
            throw new RuntimeException("Cannot write to a non-writable stream");
        }

        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException("Unable to write to stream");
        }
        
        $this->size = null;
        return $result;
    }

    public function tell(): int
    {
        if (! isset($this->stream)) {
            throw new RuntimeException("Stream is detached");
        }

        $result = ftell($this->stream);
        if ($result === null) {
            throw new RuntimeException("Unable to determine stream position");
        }

        return $result;
    }

    public function eof(): bool
    {
        if (! isset($this->stream)) {
            throw new RuntimeException("Stream is detached");
        }

        return feof($this->stream);
    }

    public function detach(): mixed
    {
        if (! isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);

        $this->uri = null;
        $this->size = null;

        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;

        return $result;
    }

    public function close(): void
    {
        if (! isset($this->stream)) {
            return;
        }

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->detach();
    }

    public function getMetadata(?string $key = null): mixed
    {
        if (! isset($this->stream)) {
            return $key ? null : [];
        }

        if (! $key) {
            return $this->metadata + stream_get_meta_data($this->stream);
        }

        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        $metadata = stream_get_meta_data($this->stream);
        return $metadata[$key] ?? null;
    }

    /**
     * Closes the stream when destructed.
     */
    public function __destruct() 
    {
        $this->close();
    }

    public function __toString(): string
    {
        if ($this->seekable) {
            $this->rewind();
        }

        return $this->contents;
    }
}