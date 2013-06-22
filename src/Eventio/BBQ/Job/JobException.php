<?php

namespace Eventio\BBQ\Job;

use Eventio\BBQ\BBQException;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class JobException extends BBQException
{

    public function __construct(JobInterface $job, $message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->job = $job;
    }

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @return JobInterface
     */
    public function getJob()
    {
        return $this->job;
    }

    public static function jobDoesNotHaveQueue($job)
    {
        return new JobException($job, "The job does not have a queue defined.");
    }

}