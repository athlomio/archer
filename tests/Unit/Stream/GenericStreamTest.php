<?php
declare(strict_types=1);

use Archer\Stream\Contract\Stream;
use Archer\Stream\GenericStream;
use Pest\Mutate\Support\StreamWrapper;

it('throws an exception if the constructor receives a non-resource', function () {
    new GenericStream(true);
})->throws(InvalidArgumentException::class);

it('initializes the stream with r+ mode and correct metadata', function () {
    $data = fopen("php://temp", "r+");
    fwrite($data, "data");

    $stream = new GenericStream($data);
    expect($stream->seekable)->toBeTrue();
    expect($stream->readable)->toBeTrue();
    expect($stream->writable)->toBeTrue();

    expect($stream->getMetadata("uri"))->toBe("php://temp");
    expect($stream->size)->toBe(4);
    expect($stream->eof())->toBeFalse();
    expect($stream->getMetadata())->toBeArray();

    $stream->close();
});

it('initializes the stream with rb+ mode and correct metadata', function () {
    $data = fopen("php://temp", "rb+");
    fwrite($data, "data");

    $stream = new GenericStream($data);
    expect($stream->seekable)->toBeTrue();
    expect($stream->readable)->toBeTrue();
    expect($stream->writable)->toBeTrue();

    expect($stream->getMetadata("uri"))->toBe("php://temp");
    expect($stream->size)->toBe(4);
    expect($stream->eof())->toBeFalse();
    expect($stream->getMetadata())->toBeArray();

    $stream->close();
});

it('closes the underlying stream when destructed', function () {
    $data = fopen("php://temp", "r");
    $stream = new GenericStream($data);

    unset($stream);
    gc_collect_cycles();

    stream_get_meta_data($data); // ← provoque une exception si $data est fermé
})->throws(TypeError::class, "stream_get_meta_data(): supplied resource is not a valid stream resource");

it('casts a seekable stream to string correctly', function () {
    $data = fopen("php://temp", "w+");
    fwrite($data, "data");

    $stream = new GenericStream($data);
    expect((string) $stream)->toBe("data");
    expect((string) $stream)->toBe("data");

    $stream->close();
});

it('casts a non-seekable stream to string correctly', function () {
    $data = popen("echo foo", "r");

    $stream = new GenericStream($data);
    expect($stream->seekable)->toBeFalse();
    expect(trim((string) $stream))->toBe("foo");

    $stream->close();
});

it('casts a partially-read non-seekable stream to remaining string content', function () {
    $data = popen("echo bar", "r");

    $stream = new GenericStream($data);
    expect($stream->seekable)->toBeFalse();
    expect($stream->read(1))->toBe("b");
    expect(trim((string) $stream))->toBe("ar");

    $stream->close();
});

it('reads stream contents correctly', function () {
    $data = fopen("php://temp", "w+");
    fwrite($data, "data");

    $stream = new GenericStream($data);
    expect($stream->contents)->toBe("");

    $stream->seek(0);
    expect($stream->contents)->toBe("data");
    expect($stream->contents)->toBe("");

    $stream->close();
});

it('checks EOF status properly', function () {
    $data = fopen("php://temp", "w+");
    fwrite($data, "data");

    $stream = new GenericStream($data);
    expect($stream->tell())->toBe(4);
    expect($stream->eof())->toBeFalse();
    expect($stream->read(1))->toBe("");
    expect($stream->eof())->toBeTrue();

    $stream->close();
});

it('returns the correct stream size', function () {
    $size = filesize(__FILE__);
    $data = fopen(__FILE__, "r");

    $stream = new GenericStream($data);
    expect($stream->size)->toBe($size);
    expect($stream->size)->toBe($size); // From cache

    $stream->close();
});

it('updates the stream size after writing', function () {
    $data = fopen("php://temp", "w+");
    expect(fwrite($data, "foo"))->toBe(3);

    $stream = new GenericStream($data);
    expect($stream->size)->toBe(3);
    expect($stream->write("test"))->toBe(4);
    expect($stream->size)->toBe(7);
    expect($stream->size)->toBe(7); // From cache

    $stream->close();
});

it('returns the current position in the stream', function () {
    $data = fopen("php://temp", "w+");

    $stream = new GenericStream($data);
    expect($stream->tell())->toBe(0);

    $stream->write("foo");
    expect($stream->tell())->toBe(3);

    $stream->seek(1);
    expect($stream->tell())->toBe(1);
    expect($stream->tell())->toBe(ftell($data));

    $stream->close();
});

function assertStreamStateAfterClosedOrDetached(Stream $stream): void
{
    expect($stream->seekable)->toBeFalse();
    expect($stream->readable)->toBeFalse();
    expect($stream->writable)->toBeFalse();

    expect($stream->size)->toBeNull();
    expect($stream->getMetadata())->toBe([]);
    expect($stream->getMetadata("foo"))->toBeNull();

    $throws = function (callable $function): void {
        try {
            $function();
        } catch (Exception $exception) {
            expect("Stream is detached")->toContain($exception->getMessage());
            return;
        }

        throw new Exception("Exception should be thrown after the stream is detached.");
    };

    $throws(fn () => $stream->read(10));
    $throws(fn () => $stream->write("bar"));
    $throws(fn () => $stream->seek(10));
    $throws(fn () => $stream->tell());
    $throws(fn () => $stream->eof());
    $throws(fn () => $stream->contents);
    $throws(fn () => (string) $stream);
}

it("detaches the stream and resets internal properties", function () {
    $data = fopen("php://temp", "r");

    $stream = new GenericStream($data);
    expect($data)->toBe($stream->detach());
    expect($data)->toBeResource();
    expect($stream->detach())->toBeNull();

    assertStreamStateAfterClosedOrDetached($stream);

    $stream->close();
});

it("closes the stream and resets internal properties", function () {
    $data = fopen("php://temp", "r");

    $stream = new GenericStream($data);
    $stream->close();

    assertStreamStateAfterClosedOrDetached($stream);
});

it("returns an empty string when reading with zero length", function () {
    $data = fopen("php://temp", "r");

    $stream = new GenericStream($data);
    expect($stream->read(0))->toBe("");

    $stream->close();
});

it("throws an exception when reading with a negative length", function () {
    $data = fopen("php://temp", "r");

    $stream = new GenericStream($data);
    $stream->read(-1);

    $stream->close();
})->throws(RuntimeException::class, "Length parameter cannot be negative");

it("detects correct read/write flags for gzip stream modes", function (string $mode, bool $readable, bool $writable) {
    $data = gzopen("php://temp", $mode);
    
    $stream = new GenericStream($data);
    expect($stream->readable)->toBe($readable);
    expect($stream->writable)->toBe($writable);

    $stream->close();
})->with([["mode" => "rb9", "readable" => true, "writable" => false], ["mode" => "wb2", "readable" => false, "writable" => true]]);

it("considers the stream readable with valid read-capable modes", function (string $mode) {
    $data = fopen("php://temp", $mode);

    $stream = new GenericStream($data);
    expect($stream->readable)->toBeTrue();

    $stream->close();
})->with(['r', 'w+', 'r+', 'x+', 'c+', 'rb', 'w+b', 'r+b', 'x+b', 'c+b', 'rt', 'w+t', 'r+t', 'x+t', 'c+t', 'a+', 'rb+']);

it("marks the stream as not readable when opened in write-only mode", function () {
    $data = fopen("php://output", "w");

    $stream = new GenericStream($data);
    expect($stream->readable)->toBeFalse();

    $stream->close();
});

it("considers the stream writable with valid write-capable modes", function (string $mode) {
    $data = fopen("php://temp", $mode);

    $stream = new GenericStream($data);
    expect($stream->writable)->toBeTrue();

    $stream->close();
})->with(['w', 'w+', 'rw', 'r+', 'x+', 'c+', 'wb', 'w+b', 'r+b', 'rb+', 'x+b', 'c+b', 'w+t', 'r+t', 'x+t', 'c+t', 'a', 'a+']);

it("marks the stream as not writable when opened in read-only mode", function () {
    $data = fopen("php://input", "r");

    $stream = new GenericStream($data);
    expect($stream->writable)->toBeFalse();

    $stream->close();
});

it("throws an exception when reading from a non-readable stream", function () {
    $data = fopen(tempnam(sys_get_temp_dir(), "archer-"), "w");

    $stream = new GenericStream($data);
    $stream->write("Hello World");
    $stream->seek(0);

    $stream->contents;
    $stream->close();
})->throws(RuntimeException::class);