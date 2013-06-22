<?php

namespace Eventio;

use Eventio\BBQ\BBQException;
use Eventio\BBQ\Job\JobException;
use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;
use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\QueueInterface;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class BBQ
{

    /**
     * @var array
     */
    private $queueRegistry = array();

    /**
     * Registers 
     * @param QueueInterface $queue
     */
    public function registerQueue(QueueInterface $queue)
    {
        $id = $queue->getId();
        if (array_key_exists($id, $this->queueRegistry)) {
            throw BBQException::queueExists($id);
        } else {
            $this->queueRegistry[$id] = $queue;
        }
    }

    /**
     * 
     * @param string $id
     * @return QueueInterface
     */
    public function getQueue($id)
    {
        if (false === array_key_exists($id, $this->queueRegistry)) {
            throw BBQException::unknownQueue($id);
        }

        return $this->queueRegistry[$id];
    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return $this->queueRegistry;
    }

    /**
     * 
     * @param QueueInterface|string $queue
     * @param JobPayloadInterface|string $job
     * @return type
     */
    public function pushJob($queue, $job)
    {
        if (!($queue instanceof QueueInterface)) {
            $queue = $this->getQueue($queue);
        }

        if (!($job instanceof JobPayloadInterface)) {
            $job = new StringPayload($job);
        }

        return $queue->pushJob($job);
    }

    /**
     * @param QueueInterface|string $queue
     * @param int|null $waitTimeout How long (seconds) we should wait for the new job from the queue
     * @return null|JobInterface
     */
    public function fetchJob($queue, $waitTimeout = 0)
    {
        if (!($queue instanceof QueueInterface)) {
            $queue = $this->getQueue($queue);
        }

        return $queue->fetchJob($waitTimeout);
    }

    /**
     * @return null
     */
    public function fetchAnyJob()
    {
        foreach ($this->queueRegistry as $queue) {
            // Checking whether there can be jobs at the queue
            if ($queue->mayHaveJob()) {
                $job = $queue->fetchJob();
                if ($job) {
                    return $job;
                }
            }
        }

        return null;
    }

    /**
     * @param JobInterface $job
     */
    public function finalizeJob(JobInterface $job)
    {
        $queue = $job->getQueue();
        if (null === $queue) {
            throw JobException::jobDoesNotHaveQueue($job);
        }
        $queue->finalizeJob($job);
    }

}