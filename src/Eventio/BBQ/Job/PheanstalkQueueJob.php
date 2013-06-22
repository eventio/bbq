<?php

namespace Eventio\BBQ\Job;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class PheanstalkQueueJob extends Job
{

    public function __construct($payload, $pheanstalkJob)
    {
        $this->setPayload($payload);
        $this->pheanstalkJob = $pheanstalkJob;
    }

    protected $pheanstalkJob;

    public function getPheanstalkJob()
    {
        return $this->pheanstalkJob;
    }

}