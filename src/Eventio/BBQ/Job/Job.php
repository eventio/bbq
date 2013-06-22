<?php

namespace Eventio\BBQ\Job;

use Eventio\BBQ\Job\Payload\JobPayloadInterface;
use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\QueueInterface;

/**
 * Generic Job class
 *
 * @author Ville Mattila <ville@eventio.fi>
 */
class Job implements JobInterface
{

    /**
     * @param JobPayloadInterface|mixed $payload Payload for the job
     */
    public function __construct($payload)
    {
        if (false === $payload instanceof JobPayloadInterface) {
            $payload = new StringPayload($payload);
        }
        $this->payload = $payload;
    }

    /**
     * 
     * @var JobPayloadInterface 
     */
    protected $payload;

    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload(JobPayloadInterface $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @var QueueInterface 
     */
    protected $queue;

    /**
     * @param QueueInterface $queue
     */
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

}