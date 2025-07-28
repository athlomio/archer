<?php
declare(strict_types=1);

namespace Archer\Http;

use Archer\Http\Contract\Response;
use Archer\Http\Enumeration\Status;

/**
 * Implementation of an outgoing, server-side response.
 * 
 * Per the HTTP specification, this interface includes properties for 
 * each of the following:
 * - Protocol version
 * - Status code and reason
 * - Headers
 * - Message body
 * 
 * Responses are considered immutable; all methods that might change 
 * state are implemented such that they retain the internal state 
 * of the current message and return an instance that contains the 
 * changed state.
 */
final class GenericResponse implements Response
{
    use MessageBehavior;

    /**
     * Response status
     * 
     * The status is an enumeration containing the status code, the 
     * status message and whether or not the status is deprecated.
     * 
     * @var Status
     */
    public protected(set) Status $status;

    public function __construct(Status $status) {
        $this->status = $status;
    }

    /**
     * Return an instance with the specified Status.
     * 
     * This method is implemented in such a way as to retain the 
     * immutability of the Response, and return an instance that 
     * has the updated status.
     * 
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * 
     * @param Status $status The status
     * @return static
     */
    public function status(Status $status): Response
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }
}