<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\Job;
use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class ArrayQueue extends AbstractQueue
{

    /**
     * @var array
     */
    protected $jobs = array();

    public function mayHaveJob()
    {
        return count($this->jobs) > 0;
    }

    public function fetchJob($timeout = null)
    {
        if (!$this->jobs) {
            return null;
        }

        $job = array_shift($this->jobs);
        $this->lockJob($job);

        return $job;
    }

    public function pushJob(JobPayloadInterface $jobPayload)
    {
        $this->jobs[] = new Job($jobPayload);
    }

    protected function init()
    {
        // Nothing to configure
    }

    public function finalizeJob(JobInterface $job)
    {
        $this->deleteLockedJob($job);
    }

}