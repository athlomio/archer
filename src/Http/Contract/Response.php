<?php
declare(strict_types=1);

namespace Archer\Http\Contract;

use Archer\Http\Enumeration\Status;

/**
 * Representation of an outgoing, server-side response.
 * 
 * Per the HTTP specification, this interface includes properties for 
 * each of the following:
 * - Protocol version
 * - Status code and reason
 * - Headers
 * - Message body
 * 
 * Responses are considered immutable; all methods that might change 
 * state MUST be implemented such that they retain the internal state 
 * of the current message and return an instance that contains the 
 * changed state.
 */
interface Response extends Message
{
    /**
     * Response status
     * 
     * The status is an enumeration containing the status code, the 
     * status message and whether or not the status is deprecated.
     * 
     * @var Status
     */
    public Status $status { get; }

    /**
     * Return an instance with the specified Status.
     * 
     * This method MUST be implemented in such a way as to retain the 
     * immutability of the Response, and MUST return an instance that 
     * has the updated status.
     * 
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * 
     * @param Status $status The status
     * @return static
     */
    public function status(Status $status): static;
}