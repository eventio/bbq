<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\IronMQQueueJob;
use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class IronMQQueue extends AbstractQueue
{

    /**
     * @var \IronMQ
     */
    protected $ironMQ;

    /**
     * @var string 
     */
    protected $tube;

    public function __construct($id, \IronMQ $ironMQ, $queue_name, array $config = array())
    {
        $this->ironMQ = $ironMQ;
        $this->queue_name = $queue_name;

        parent::__construct($id, $config);
    }

    protected function init()
    {
        
    }

    public function fetchJob($timeout = null)
    {
        $ironMqMessage = $this->ironMQ->getMessage($this->queue_name);
        if (!$ironMqMessage) {
            return null;
        }

        $job = new IronMQQueueJob($ironMqMessage);
        $job->setQueue($this);

        return $job;
    }

    public function finalizeJob(JobInterface $job)
    {
        $this->ironMQ->deleteMessage($this->queue_name, $job->getIronMQMessageId());
    }

    public function pushJob(JobPayloadInterface $jobPayload)
    {
        $this->ironMQ->postMessage($this->queue_name, serialize($jobPayload));
    }

    public function releaseJob(JobInterface $job)
    {
        $this->ironMQ->releaseMessage($this->queue_name, $job->getIronMQMessageId());
    }

}