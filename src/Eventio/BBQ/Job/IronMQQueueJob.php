<?php

namespace Eventio\BBQ\Job;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class IronMQQueueJob extends Job
{

    public function __construct($ironMqMessageArray)
    {
        $this->setPayload(unserialize($ironMqMessageArray->body));
        $this->ironMQMessageId = $ironMqMessageArray->id;
    }

    /**
     * @var string
     */
    protected $ironMQMessageId;

    /**
     * @return string
     */
    public function getIronMQMessageId()
    {
        return $this->ironMQMessageId;
    }

}