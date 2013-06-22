<?php

namespace Eventio\BBQ\Job\Payload;

/**
 * Generic payload class
 *
 * @author Ville Mattila <ville@eventio.fi>
 */
class StringPayload implements JobPayloadInterface
{

    protected $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function serialize()
    {
        return $this->string;
    }

    public function unserialize($serialized)
    {
        $this->string = $serialized;
    }

    public function __toString()
    {
        return $this->string;
    }

}
