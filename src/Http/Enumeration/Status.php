<?php
declare(strict_types=1);

namespace Archer\Http\Enumeration;

use LogicException;

/**
 * HTTP response status
 * 
 * This enumeration stores the status codes and messages for all HTTP
 * statuses.
 * 
 * The status codes listed below are defined by `RFC 9110`
 * @see https://httpwg.org/specs/rfc9110.html
 */
enum Status {
    /**
     * This interim response indicates that the client should continue
     * the request or ignore the response if the request is already
     * finished.
     */
    case CONTINUE;

    /**
     * This code is sent in response to an `Upgrade` request header 
     * from the client and indicates the protocol the server is 
     * switching to.
     */
    case SWITCHING_PROTOCOLS;

    /**
     * This code was used in WebDAV contexts to indicate that a request
     * has been received by the server, but no status was available at
     * the time of the response.
     * 
     * @deprecated This feature is no longer recommended.
     * @since RFC 4918
     */
    case PROCESSING;

    /**
     * This status code is primarily intended to be used with the 
     * `Link` header, letting the user agent start `preloading` 
     * resources while the server prepares a response or `preconnect` 
     * to an origin from which the page will need resources.
     */
    case EARLY_HINTS;

    /**
     * The request succeeded. The result and meaning of "success" 
     * depends on the HTTP method:
     * - `GET`: The resource has been fetched and transmitted in the 
     * message body.
     * - `HEAD`: Representation headers are included in the response 
     * without any message body.
     * - `PUT` or `POST`: The resource describing the result of the 
     * action is transmitted in the message body.
     * - `TRACE`: The message body contains the request as received by 
     * the server.
     */
    case OK;

    /**
     * The request succeeded, and a new resource was created as a 
     * result. This is typically the response sent after `POST` 
     * requests, or some `PUT` requests.
     */
    case CREATED;

    /**
     * The request has been received but not yet acted upon. It is 
     * noncommittal, since there is no way in HTTP to later send an 
     * asynchronous response indicating the outcome of the request. It 
     * is intended for cases where another process or server handles 
     * the request, or for batch processing.
     */
    case ACCEPTED;

    /**
     * This response code means the returned metadata is not exactly 
     * the same as is available from the origin server, but is 
     * collected from a local or a third-party copy. This is mostly 
     * used for mirrors or backups of another resource. Except for that
     * specific case, the `200 OK` response is preferred to this status.
     */
    case NON_AUTHORITATIVE_INFORMATION;

    /**
     * There is no content to send for this request, but the headers 
     * are useful. The user agent may update its cached headers for 
     * this resource with the new ones.
     */
    case NO_CONTENT;

    /**
     * Tells the user agent to reset the document which sent this 
     * request.
     */
    case RESET_CONTENT;

    /**
     * This response code is used in response to a `range request` 
     * when the client has requested a part or parts of a resource.
     */
    case PARTIAL_CONTENT;

    /**
     * Conveys information about multiple resources, for situations 
     * where multiple status codes might be appropriate.
     */
    case MULTI_STATUS;

    /**
     * Used inside a `<dav:propstat>` response element to avoid 
     * repeatedly enumerating the internal members of multiple 
     * bindings to the same collection.
     */
    case ALREADY_REPORTED;

    /**
     * The server has fulfilled a `GET` request for the resource, 
     * and the response is a representation of the result of one or 
     * more instance-manipulations applied to the current instance.
     */
    case IM_USED;

    /**
     * In `agent-driven content negotiation`, the request has more than
     * one possible response and the user agent or user should choose 
     * one of them. There is no standardized way for clients to 
     * automatically choose one of the responses, so this is rarely 
     * used.
     */
    case MULTIPLE_CHOICES;

    /**
     * The URL of the requested resource has been changed permanently. 
     * The new URL is given in the response.
     */
    case MOVED_PERMANENTLY;

    /**
     * This response code means that the URI of requested resource has 
     * been changed temporarily. Further changes in the URI might be 
     * made in the future, so the same URI should be used by the client
     * in future requests.
     */
    case FOUND;

    /**
     * The server sent this response to direct the client to get the 
     * requested resource at another URI with a `GET` request.
     */
    case SEE_OTHER;
    
    /**
     * This is used for caching purposes. It tells the client that the 
     * response has not been modified, so the client can continue to 
     * use the same `cached` version of the response.
     */
    case NOT_MODIFIED;

    /**
     * Defined in a previous version of the HTTP specification to 
     * indicate that a requested response must be accessed by a proxy. 
     * It has been deprecated due to security concerns regarding 
     * in-band configuration of a proxy.
     * 
     * @deprecated This feature is no longer recommended.
     */
    case USE_PROXY;

    /**
     * This response code is no longer used; but is reserved. It was 
     * used in a previous version of the HTTP/1.1 specification.
     * 
     * @deprecated This feature is no longer recommended.
     * @since HTTP/1.1
     */
    case UNUSED;

    /**
     * The server sends this response to direct the client to get the 
     * requested resource at another URI with the same method that was 
     * used in the prior request. This has the same semantics as the 
     * `302 Found` response code, with the exception that the user 
     * agent must not change the HTTP method used: if a `POST` was used
     * in the first request, a `POST` must be used in the redirected 
     * request.
     */
    case TEMPORARY_REDIRECT;

    /**
     * This means that the resource is now permanently located at 
     * another URI, specified by the `Location` response header. This 
     * has the same semantics as the `301 Moved Permanently` HTTP 
     * response code, with the exception that the user agent must not 
     * change the HTTP method used: if a `POST` was used in the first 
     * request, a `POST` must be used in the second request.
     */
    case PERMANENT_REDIRECT;

    /**
     * The server cannot or will not process the request due to 
     * something that is perceived to be a client error (e.g., 
     * malformed request syntax, invalid request message framing, or 
     * deceptive request routing).
     */
    case BAD_REQUEST;

    /**
     * Although the HTTP standard specifies "unauthorized", 
     * semantically this response means "unauthenticated". That is, 
     * the client must authenticate itself to get the requested 
     * response.
     */
    case UNAUTHORIZED;

    /**
     * The initial purpose of this code was for digital payment 
     * systems, however this status code is rarely used and no 
     * standard convention exists.
     */
    case PAYMENT_REQUIRED;

    /**
     * The client does not have access rights to the content; that is, 
     * it is unauthorized, so the server is refusing to give the 
     * requested resource. Unlike `401 Unauthorized`, the client's 
     * identity is known to the server.
     */
    case FORBIDDEN;

    /**
     * The server cannot find the requested resource. In the browser, 
     * this means the URL is not recognized. In an API, this can also 
     * mean that the endpoint is valid but the resource itself does not
     *  exist. Servers may also send this response instead of `403 
     * Forbidden` to hide the existence of a resource from an 
     * unauthorized client. This response code is probably the most 
     * well known due to its frequent occurrence on the web.
     */
    case NOT_FOUND;

    /**
     * The `request method` is known by the server but is not supported
     * by the target resource. For example, an API may not allow 
     * `DELETE` on a resource, or the `TRACE` method entirely.
     */
    case METHOD_NOT_ALLOWED;

    /**
     * This response is sent when the web server, after performing 
     * `server-driven content negotiation`, doesn't find any content 
     * that conforms to the criteria given by the user agent.
     */
    case NOT_ACCEPTABLE;

    /**
     * This is similar to `401 Unauthorized` but authentication is 
     * needed to be done by a proxy.
     */
    case PROXY_AUTHENTICATION_REQUIRED;

    /**
     * This response is sent on an idle connection by some servers, 
     * even without any previous request by the client. It means that 
     * the server would like to shut down this unused connection. This 
     * response is used much more since some browsers use HTTP 
     * pre-connection mechanisms to speed up browsing. Some servers 
     * may shut down a connection without sending this message.
     */
    case REQUEST_TIMEOUT;

    /**
     * This response is sent when a request conflicts with the current 
     * state of the server. In `WebDAV` remote web authoring, `409` 
     * responses are errors sent to the client so that a user might be 
     * able to resolve a conflict and resubmit the request.
     */
    case CONFLICT;

    /**
     * This response is sent when the requested content has been 
     * permanently deleted from server, with no forwarding address. 
     * Clients are expected to remove their caches and links to the 
     * resource. The HTTP specification intends this status code to be 
     * used for "limited-time, promotional services". APIs should not 
     * feel compelled to indicate resources that have been deleted with
     *  this status code.
     */
    case GONE;

    /**
     * Server rejected the request because the `Content-Length` header 
     * field is not defined and the server requires it.
     */
    case LENGTH_REQUIRED;

    /**
     * In `conditional requests`, the client has indicated 
     * preconditions in its headers which the server does not meet.
     */
    case PRECONDITION_FAILED;

    /**
     * The request body is larger than limits defined by server. The 
     * server might close the connection or return an `Retry-After` 
     * header field.
     */
    case CONTENT_TOO_LARGE;

    /**
     * The URI requested by the client is longer than the server is 
     * willing to interpret.
     */
    case URI_TOO_LONG;

    /**
     * The media format of the requested data is not supported by the 
     * server, so the server is rejecting the request.
     */
    case UNSUPPORTED_MEDIA_TYPE;

    /**
     * The `ranges` specified by the `Range` header field in the 
     * request cannot be fulfilled. It's possible that the range is 
     * outside the size of the target resource's data.
     */
    case RANGE_NOT_SATISFIABLE;

    /**
     * This response code means the expectation indicated by the 
     * `Expect` request header field cannot be met by the server.
     */
    case EXPECTATION_FAILED;

    /**
     * The server refuses the attempt to brew coffee with a teapot.
     */
    case IM_A_TEAPOT;

    /**
     * The request was directed at a server that is not able to 
     * produce a response. This can be sent by a server that is not 
     * configured to produce responses for the combination of scheme 
     * and authority that are included in the request URI.
     */
    case MISDIRECTED_REQUEST;

    /**
     * The request was well-formed but was unable to be followed due 
     * to semantic errors.
     */
    case UNPROCESSABLE_CONTENT;

    /**
     * The resource that is being accessed is locked.
     */
    case LOCKED;

    /**
     * The request failed due to failure of a previous request.
     */
    case FAILED_DEPENDENCY;

    /**
     * Indicates that the server is unwilling to risk processing a 
     * request that might be replayed.
     */
    case TOO_EARLY;

    /**
     * The server refuses to perform the request using the current 
     * protocol but might be willing to do so after the client upgrades
     * to a different protocol. The server sends an `Upgrade` header 
     * in a `426` response to indicate the required protocol(s).
     */
    case UPGRADE_REQUIRED;

    /**
     * The origin server requires the request to be `conditional`. 
     * This response is intended to prevent the 'lost update' problem, 
     * where a client `GET`s a resource's state, modifies it and `PUT`s
     *  it back to the server, when meanwhile a third party has 
     * modified the state on the server, leading to a conflict.
     */
    case PRECONDITION_REQUIRED;

    /**
     * The user has sent too many requests in a given amount of time 
     * (`rate limiting`).
     */
    case TOO_MANY_REQUESTS;

    /**
     * The server is unwilling to process the request because its 
     * header fields are too large. The request may be resubmitted 
     * after reducing the size of the request header fields.
     */
    case REQUEST_HEADER_FIELDS_TOO_LARGE;

    /**
     * The user agent requested a resource that cannot legally be 
     * provided, such as a web page censored by a government.
     */
    case UNAVAILABLE_FOR_LEGAL_REASONS;

    /**
     * The server has encountered a situation it does not know how to 
     * handle. This error is generic, indicating that the server cannot
     * find a more appropriate `5XX` status code to respond with.
     */
    case INTERNAL_SERVER_ERROR;

    /**
     * The request method is not supported by the server and cannot be 
     * handled. The only methods that servers are required to support 
     * (and therefore that must not return this code) are `GET` and 
     * `HEAD`.
     */
    case NOT_IMPLEMENTED;

    /**
     * This error response means that the server, while working as a 
     * gateway to get a response needed to handle the request, got an 
     * invalid response.
     */
    case BAD_GATEWAY;

    /**
     * The server is not ready to handle the request. Common causes 
     * are a server that is down for maintenance or that is overloaded.
     * Note that together with this response, a user-friendly page 
     * explaining the problem should be sent. This response should be 
     * used for temporary conditions and the `Retry-After` HTTP header 
     * should, if possible, contain the estimated time before the 
     * recovery of the service. The webmaster must also take care about 
     * the caching-related headers that are sent along with this 
     * response, as these temporary condition responses should usually 
     * not be cached.
     */
    case SERVICE_UNAVAILABLE;

    /**
     * This error response is given when the server is acting as a 
     * gateway and cannot get a response in time.
     */
    case GATEWAY_TIMEOUT;

    /**
     * The HTTP version used in the request is not supported by the 
     * server.
     */
    case HTTP_VERSION_NOT_SUPPORTED;

    /**
     * The server has an internal configuration error: during content 
     * negotiation, the chosen variant is configured to engage in 
     * content negotiation itself, which results in circular references
     * when creating responses.
     */
    case VARIANT_ALSO_NEGOTIATES;

    /**
     * The method could not be performed on the resource because the
     * server is unable to store the representation needed to 
     * successfully complete the request.
     */
    case INSUFFICIENT_STORAGE;

    /**
     * The server detected an infinite loop while processing the 
     * request.
     */
    case LOOP_DETECTED;

    /**
     * The client request declares an HTTP Extension (`RFC 2774`) that 
     * should be used to process the request, but the extension is not 
     * supported.
     */
    case NOT_EXTENDED;

    /**
     * Indicates that the client needs to authenticate to gain network 
     * access.
     */
    case NETWORK_AUTHENTICATION_REQUIRED;

    /**
     * The code method returns the HTTP status code for a given status.
     * 
     * HTTP response status codes indicate whether a specific HTTP 
     * request has been successfully completed. Responses are grouped 
     * in five classes:
     * - Informational responses (`100 – 199`)
     * - Successful responses (`200 – 299`)
     * - Redirection messages (`300 – 399`)
     * - Client error responses (`400 – 499`)
     * - Server error responses (`500 – 599`)
     * 
     * @return int The status code
     */
    public function code(): int
    {
        return match($this) {
            self::CONTINUE => 100,
            self::SWITCHING_PROTOCOLS => 101,
            self::PROCESSING => 102,
            self::EARLY_HINTS => 103,

            self::OK => 200,
            self::CREATED => 201,
            self::ACCEPTED => 202,
            self::NON_AUTHORITATIVE_INFORMATION => 203,
            self::NO_CONTENT => 204,
            self::RESET_CONTENT => 205,
            self::PARTIAL_CONTENT => 206,
            self::MULTI_STATUS => 207,
            self::ALREADY_REPORTED => 208,
            self::IM_USED => 226,

            self::MULTIPLE_CHOICES => 300,
            self::MOVED_PERMANENTLY => 301,
            self::FOUND => 302,
            self::SEE_OTHER => 303,
            self::NOT_MODIFIED => 304,
            self::USE_PROXY => 305,
            self::UNUSED => 306,
            self::TEMPORARY_REDIRECT => 307,
            self::PERMANENT_REDIRECT => 308,

            self::BAD_REQUEST => 400,
            self::UNAUTHORIZED => 401,
            self::PAYMENT_REQUIRED => 402,
            self::FORBIDDEN => 403,
            self::NOT_FOUND => 404,
            self::METHOD_NOT_ALLOWED => 405,
            self::NOT_ACCEPTABLE => 406,
            self::PROXY_AUTHENTICATION_REQUIRED => 407,
            self::REQUEST_TIMEOUT => 408,
            self::CONFLICT => 409,
            self::GONE => 410,
            self::LENGTH_REQUIRED => 411,
            self::PRECONDITION_FAILED => 412,
            self::CONTENT_TOO_LARGE => 413,
            self::URI_TOO_LONG => 414,
            self::UNSUPPORTED_MEDIA_TYPE => 415,
            self::RANGE_NOT_SATISFIABLE => 416,
            self::EXPECTATION_FAILED => 417,
            self::IM_A_TEAPOT => 418,
            self::MISDIRECTED_REQUEST => 421,
            self::UNPROCESSABLE_CONTENT => 422,
            self::LOCKED => 423,
            self::FAILED_DEPENDENCY => 424,
            self::TOO_EARLY => 425,
            self::UPGRADE_REQUIRED => 426,
            self::PRECONDITION_REQUIRED => 428,
            self::TOO_MANY_REQUESTS => 429,
            self::REQUEST_HEADER_FIELDS_TOO_LARGE => 431,
            self::UNAVAILABLE_FOR_LEGAL_REASONS => 451,

            self::INTERNAL_SERVER_ERROR => 500,
            self::NOT_IMPLEMENTED => 501,
            self::BAD_GATEWAY => 502,
            self::SERVICE_UNAVAILABLE => 503,
            self::GATEWAY_TIMEOUT => 504,
            self::HTTP_VERSION_NOT_SUPPORTED => 505,
            self::VARIANT_ALSO_NEGOTIATES => 506,
            self::INSUFFICIENT_STORAGE => 507,
            self::LOOP_DETECTED => 508,
            self::NOT_EXTENDED => 510,
            self::NETWORK_AUTHENTICATION_REQUIRED => 511,

            default => throw new LogicException(message: "Unhandled HTTP status case: {$this->name}")
        };
    }

    /**
     * The message method returns the HTTP status message for a given
     * status.
     * 
     * The status message is a human readable string of the statuses 
     * stored in this enumeration.
     * 
     * @return string HTTP status message
     */
    public function message(): string
    {
        return match ($this) {
            self::CONTINUE => "Continue",
            self::SWITCHING_PROTOCOLS => "Switching protocols",
            self::PROCESSING => "Processing",
            self::EARLY_HINTS => "Early hints",

            self::OK => "OK",
            self::CREATED => "Created",
            self::ACCEPTED => "Accepted",
            self::NON_AUTHORITATIVE_INFORMATION => "Non-authoritative information",
            self::NO_CONTENT => "No content",
            self::RESET_CONTENT => "Reset content",
            self::PARTIAL_CONTENT => "Partial content",
            self::MULTI_STATUS => "Multi-status",
            self::ALREADY_REPORTED => "Already reported",
            self::IM_USED => "Instance Manipulation used (delta encoding)",

            self::MULTIPLE_CHOICES => "Multiple choices",
            self::MOVED_PERMANENTLY => "Moved permanently",
            self::FOUND => "Found",
            self::SEE_OTHER => "See other (redirect)",
            self::NOT_MODIFIED => "Not modified",
            self::USE_PROXY => "Use proxy",
            self::UNUSED => "Unused",
            self::TEMPORARY_REDIRECT => "Temporary redirect",
            self::PERMANENT_REDIRECT => "Permanent redirect",

            self::BAD_REQUEST => "Bad request",
            self::UNAUTHORIZED => "Unauthorized",
            self::PAYMENT_REQUIRED => "Payment required",
            self::FORBIDDEN => "Forbidden",
            self::NOT_FOUND => "Not found",
            self::METHOD_NOT_ALLOWED => "Method not allowed",
            self::NOT_ACCEPTABLE => "Not acceptable",
            self::PROXY_AUTHENTICATION_REQUIRED => "Proxy authentication required",
            self::REQUEST_TIMEOUT => "Request timeout",
            self::CONFLICT => "Conflict",
            self::GONE => "Gone",
            self::LENGTH_REQUIRED => "Length required",
            self::PRECONDITION_FAILED => "Precondition failed",
            self::CONTENT_TOO_LARGE => "Content too large",
            self::URI_TOO_LONG => "URI too long",
            self::UNSUPPORTED_MEDIA_TYPE => "Unsupported media type",
            self::RANGE_NOT_SATISFIABLE => "Range not satisfiable",
            self::EXPECTATION_FAILED => "Expectation failed",
            self::IM_A_TEAPOT => "I'm a teapot",
            self::MISDIRECTED_REQUEST => "Misdirected request",
            self::UNPROCESSABLE_CONTENT => "Unprocessable content",
            self::LOCKED => "Locked",
            self::FAILED_DEPENDENCY => "Failed dependency",
            self::TOO_EARLY => "Too early",
            self::UPGRADE_REQUIRED => "Upgrade required",
            self::PRECONDITION_REQUIRED => "Precondition required",
            self::TOO_MANY_REQUESTS => "Too many requests",
            self::REQUEST_HEADER_FIELDS_TOO_LARGE => "Request header fields too large",
            self::UNAVAILABLE_FOR_LEGAL_REASONS => "Unavailable for legal reasons",

            self::INTERNAL_SERVER_ERROR => "Internal server error",
            self::NOT_IMPLEMENTED => "Not implemented",
            self::BAD_GATEWAY => "Bad gateway",
            self::SERVICE_UNAVAILABLE => "Service unavailable",
            self::GATEWAY_TIMEOUT => "Gateway timeout",
            self::HTTP_VERSION_NOT_SUPPORTED => "HTTP version not supported",
            self::VARIANT_ALSO_NEGOTIATES => "Variant also negotiates",
            self::INSUFFICIENT_STORAGE => "Insufficient storage",
            self::LOOP_DETECTED => "Loop detected",
            self::NOT_EXTENDED => "Not extended",
            self::NETWORK_AUTHENTICATION_REQUIRED => "Network authentication required",

            default => throw new LogicException(message: "Unhandled HTTP status case: {$this->name}")
        };
    }

    /**
     * The deprecated method returns wether or not the status is
     * deprecated.
     * 
     * @return bool
     */
    public function deprecated(): bool
    {
        return match ($this) {
            self::PROCESSING, self::USE_PROXY, self::UNUSED => true,
            default => false,
        };
    }
}