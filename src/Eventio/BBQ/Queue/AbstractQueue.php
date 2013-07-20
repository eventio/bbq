<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\JobInterface;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
abstract class AbstractQueue implements QueueInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array 
     */
    protected $config;

    public function __construct($id, array $config = array())
    {
        $this->id = $id;
        $this->config = $config;

        $this->init();
    }

    public function getId()
    {
        return $this->id;
    }

    public function mayHaveJob()
    {
        return true;
    }

    /**
     * @var array
     */
    protected $lockedJobs = array();

    /**
     * @param \Eventio\BBQ\Queue\JobInterface $job
     */
    protected function lockJob(JobInterface $job)
    {
        $this->lockedJobs[spl_object_hash($job)] = $job;
    }

    /**
     * @param \Eventio\BBQ\Queue\JobInterface $job
     * @return type
     */
    protected function deleteLockedJob(JobInterface $job)
    {
        unset($this->lockedJobs[spl_object_hash($job)]);
    }

    abstract protected function init();

    /**
     * @param \Eventio\BBQ\Queue\JobInterface $job
     */
    public function releaseJob(JobInterface $job)
    {
        $this->deleteLockedJob($job);

        return $this->pushJob($job->getPayload());
    }

    public function hasLockedJobs()
    {
        return (count($this->lockedJobs) > 0);
    }

    public function releaseLockedJobs()
    {
        foreach ($this->lockedJobs as $lockedJob) {
            $this->releaseJob($lockedJob);
        }
    }
}