<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;

/**
 *
 * @author Ville Mattila <ville@eventio.fi>
 */
interface QueueInterface
{

    /**
     * @return JobInterface|null
     * @param int|null $timeout
     */
    public function fetchJob($timeout = null);

    /**
     * @param JobPayloadInterface $jobPayload
     */
    public function pushJob(JobPayloadInterface $jobPayload);

    /**
     * @param JobInterface $job
     */
    public function finalizeJob(JobInterface $job);

    /**
     * @param JobInterface $job
     */
    public function releaseJob(JobInterface $job);

    /**
     * @param boolean
     */
    public function hasLockedJobs();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return bool
     */
    public function mayHaveJob();
}