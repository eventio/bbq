<?php

namespace Eventio\BBQ\Job;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class DirectoryQueueJob extends Job
{

    public function __construct($payload, $filePath)
    {
        $this->setPayload($payload);
        $this->setJobFilePath($filePath);
    }

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @return string
     */
    public function getJobFilePath()
    {
        return $this->filePath;
    }

    public function setJobFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

}