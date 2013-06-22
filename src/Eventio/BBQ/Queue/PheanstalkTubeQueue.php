<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;
use Eventio\BBQ\Job\PheanstalkQueueJob;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class PheanstalkTubeQueue extends AbstractQueue
{

    /**
     * @var \Pheanstalk_Pheanstalk
     */
    protected $pheanstalk;

    /**
     * @var string 
     */
    protected $tube;

    public function __construct($id, \Pheanstalk_Pheanstalk $pheanstalk, $tube, array $config = array())
    {
        $this->pheanstalk = $pheanstalk;
        $this->tube = $tube;

        parent::__construct($id, $config);
    }

    protected function init()
    {
        
    }

    public function fetchJob($timeout = null)
    {
        $pheanstalkJob = $this->pheanstalk->watch($this->tube)->ignore('default')->reserve($timeout);
        if (!$pheanstalkJob) {
            return null;
        }

        $job = new PheanstalkQueueJob(unserialize($pheanstalkJob->getData()), $pheanstalkJob);
        $job->setQueue($this);

        return $job;
    }

    public function finalizeJob(JobInterface $job)
    {
        $this->pheanstalk->delete($job->getPheanstalkJob());
    }

    public function pushJob(JobPayloadInterface $jobPayload)
    {
        $this->pheanstalk->useTube($this->tube)->put(serialize($jobPayload));
    }

    public function releaseJob(JobInterface $job)
    {
        $this->pheanstalk->release($job->getPheanstalkJob());
    }

}