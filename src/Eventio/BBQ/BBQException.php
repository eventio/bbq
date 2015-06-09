<?php

namespace Eventio\BBQ;

/**
 * Generic BBQ Library exception
 * 
 * @author Ville Mattila <ville@eventio.fi>
 */
class BBQException extends \Exception
{

    public static function queueExists($queueId)
    {
        throw new BBQException(sprintf("Queue with id '%s' is already registered.", $queueId));
    }

    public static function unknownQueue($queueId)
    {
        throw new BBQException(sprintf("There is no queue registered with ID '%s'.", $queueId));
    }

}
