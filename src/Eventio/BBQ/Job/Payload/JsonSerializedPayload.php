<?php

namespace Eventio\BBQ\Job\Payload;

/**
 * Generic payload class
 *
 * @author Ville Mattila <ville@eventio.fi>
 */
class JsonSerializedPayload implements JobPayloadInterface
{
    public function serialize()
    {
        $vars = get_object_vars($this);
        
        $serializedVars = array();
        foreach ($vars as $var => $value) {
            $serializedVars[$var] = serialize($value);
        }
        
        return json_encode($serializedVars);
    }

    public function unserialize($serialized)
    {
        $properties = json_decode($serialized, true);
        foreach ($properties as $property => $value) {
            $this->$property = unserialize($value);
        }
    }
}
