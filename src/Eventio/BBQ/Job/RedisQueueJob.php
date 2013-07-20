<?php

namespace Eventio\BBQ\Job;

use Eventio\BBQ\Job\Job;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class RedisQueueJob extends Job
{

    public function __construct($payload, $rawData, $processingKey = null)
    {
        parent::__construct($payload);
        $this->rawData = $rawData;
        $this->processingKey = $processingKey;
    }

    protected $rawData;

    public function getRawData()
    {
        return $this->rawData;
    }

    protected $processingKey;

    public function getProcessingKey()
    {
        return $this->processingKey;
    }

}