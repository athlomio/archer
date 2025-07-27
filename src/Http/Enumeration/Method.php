<?php
declare(strict_types=1);

namespace Archer\Http\Enumeration;

/**
 * HTTP request methods
 * 
 * HTTP defines a set of request methods to indicate the purpose of the 
 * request and what is expected if the request is successful. Although 
 * they can also be nouns, these request methods are sometimes referred
 * to as HTTP verbs. Each request method has its own semantics, but 
 * some characteristics are shared across multiple methods, 
 * specifically request methods can be `safe`, `idempotent`, or 
 * `cacheable`.
 * 
 * This enumeration stores the methods and semantics.
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods
 */
enum Method
{
    /**
     * The `GET` method requests a representation of the specified 
     * resource. Requests using `GET` should only retrieve data and 
     * should not contain a request `content`.
     */
    case GET;

    /**
     * The `POST` method submits an entity to the specified resource, 
     * often causing a change in state or side effects on the server.
     */
    case POST;

    /**
     * The `PUT` method replaces all current representations of the 
     * target resource with the request `content`.
     */
    case PUT;

    /**
     * The `PATCH` method applies partial modifications to a resource.
     */
    case PATCH;

    /**
     * The `DELETE` method deletes the specified resource.
     */
    case DELETE;
    
    /**
     * The `HEAD` method asks for a response identical to a `GET` 
     * request, but without a response body.
     */
    case HEAD;

    /**
     * The `CONNECT` method establishes a tunnel to the server 
     * identified by the target resource.
     */
    case CONNECT;

    /**
     * The `OPTIONS` method describes the communication options for 
     * the target resource.
     */
    case OPTIONS;

    /**
     * The `TRACE` method performs a message loop-back test along the 
     * path to the target resource.
     */
    case TRACE;

    /**
     * The safe method return wether or not a method is safe.
     * 
     * An HTTP method is safe if it doesn't alter the state of the 
     * server. In other words, a method is safe if it leads to a 
     * read-only operation. All safe methods are also idempotent, but 
     * not all idempotent methods are safe.
     * 
     * Even if safe methods have a read-only semantic, servers can 
     * alter their state: e.g., they can log or keep statistics. What 
     * is important here is that by calling a safe method, the client 
     * doesn't request any server change itself, and therefore won't 
     * create an unnecessary load or burden for the server. Browsers 
     * can call safe methods without fearing to cause any harm to the 
     * server; this allows them to perform activities like pre-fetching
     * without risk. Web crawlers also rely on calling safe methods.
     *
     * Safe methods don't need to serve static files only; a server 
     * can generate an answer to a safe method on-the-fly, as long as 
     * the generating script guarantees safety: it should not trigger 
     * external effects, like triggering an order in an e-commerce 
     * website.
     * 
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Safe/HTTP
     * @return bool
     */
    public function safe(): bool
    {
        return match ($this) {
            self::GET, self::HEAD, self::OPTIONS, self::TRACE => true,
            default => false,
        };
    }

    /**
     * The idempotent method return wether or not a method is
     * idempotent.
     * 
     * An HTTP method is idempotent if the intended effect on the 
     * server of making a single request is the same as the effect of 
     * making several identical requests.
     * 
     * A client can safely retry a request that uses an idempotent 
     * method, for example, in cases where there is doubt as to whether
     * the request reached the server. If multiple identical requests
     * happen to reach the server, as long as the method is idempotent,
     * no harm is done.
     * 
     * The HTTP specification only defines idempotency in terms of the 
     * intended effect of the client on the server. For example, a POST
     * request intends to send data to the server, whereas a DELETE 
     * request intends to delete a resource on the server. In practice,
     * it falls to the server to make sure the routes it exposes adhere
     * to these semantics.
     * 
     * Also, bear in mind:
     * - A request with an idempotent method does not necessarily mean 
     * that the request has no side effects on the server, only that 
     * the client intended none: For example, the server may log the 
     * time each request is received.
     * - The response returned by each request may differ: for example,
     * the first call of a DELETE will likely return a 200, 
     * while successive ones will likely return a 404.
     * 
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Idempotent
     * @return bool
     */
    public function idempotent(): bool
    {
        return match ($this) {
            self::GET, self::HEAD, self::OPTIONS, 
            self::TRACE, self::PUT, self::DELETE => true,
            default => false,
        };
    }

    /**
     * The cacheable method return wether or not a method is cacheable.
     * 
     * A cacheable response is an HTTP response that can be cached, 
     * that is stored to be retrieved and used later, saving a new 
     * request to the server. Not all HTTP responses can be cached; 
     * these are the constraints for an HTTP response to be cacheable:
     * - The method used in the request is cacheable, that is either a 
     * `GET` or a `HEAD` method. A response to a `POST` or `PATCH` 
     * request can also be cached if freshness is indicated and the 
     * `Content-Location` header is set, but this is rarely implemented.
     * For example, Firefox does not support it (`Firefox bug 109553`).
     * Other methods, like `PUT` or `DELETE` are not cacheable and their
     * result cannot be cached.
     * - The status code of the response is known by the application 
     * caching, and is cacheable. The following status codes are 
     * cacheable: `200`, `203`, `204`, `206`, `300`, `301`, `404`, 
     * `405`, `410`, `414`, and `501`.
     * - There are no specific headers in the response, like 
     * `Cache-Control`, with values that would prohibit caching.
     * 
     * Note that some requests with non-cacheable responses to a 
     * specific URI may invalidate previously cached responses from 
     * the same URI. For example, a `PUT` to `/pageX.html` will 
     * invalidate all cached responses to `GET` or `HEAD` requests to 
     * `/pageX.html`.
     * 
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Cacheable
     * @see https://bugzilla.mozilla.org/show_bug.cgi?id=109553
     * 
     * @return bool
     */
    public function cacheable(): bool
    {
        return match ($this) {
            self::GET, self::HEAD => true,
            self::PUT, self::PATCH => true,
            default => false,
        };
    }
}