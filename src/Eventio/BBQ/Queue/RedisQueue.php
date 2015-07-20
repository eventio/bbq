<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;
use Eventio\BBQ\Job\RedisQueueJob;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class RedisQueue extends AbstractQueue
{

    /**
     * @var \Predis\ClientInterface
     */
    protected $predis;

    /**
     * @var string 
     */
    protected $queueKey;

    /**
     * @var string 
     */
    protected $processingQueueKeyPrefix;

    public function __construct($id, \Predis\ClientInterface $predis, $queueKey, array $config = array())
    {
        $this->predis = $predis;
        $this->queueKey = $queueKey;

        parent::__construct($id, $config);
    }

    protected function init()
    {
        $this->config = array_merge(array(
            'processing_queue_key_prefix' => '%q:%h:%p',
            'allow_infinite_blocking' => false,
            'skip_shutdown_release' => false
        ), $this->config);

        $this->processingQueueKeyPrefix = $this->convertProcessingKey($this->config['processing_queue_key_prefix']);
        
        if ($this->config['skip_shutdown_release'] == false) {
            register_shutdown_function(array($this, 'releaseLockedJobs'));
        }
    }

    private function convertProcessingKey($template)
    {
        $replaces = array(
            '%q' => $this->queueKey,
            '%h' => gethostname(),
            '%p' => getmypid(),
        );
        return str_replace(array_keys($replaces), array_values($replaces), $template);
    }

    public function fetchJob($timeout = null)
    {
        $processingKey = uniqid($this->processingQueueKeyPrefix);
        
        if (false === $this->config['allow_infinite_blocking'] && !$timeout) {
            $redisJob = $this->predis->rpoplpush($this->queueKey, $processingKey);
        } else {
            $redisJob = $this->predis->brpoplpush($this->queueKey, $processingKey, $timeout);
        }
        if (!$redisJob) {
            return null;
        }

        $job = new RedisQueueJob(unserialize($redisJob), $redisJob, $processingKey);
        $job->setQueue($this);

        $this->lockJob($job);
        return $job;
    }

    public function finalizeJob(JobInterface $job)
    {
        // 
        $this->predis->lrem($job->getProcessingKey(), 0, $job->getRawData());
        $this->deleteLockedJob($job);
    }

    public function pushJob(JobPayloadInterface $jobPayload)
    {
        // Adds a new job to the main queue
        $this->predis->lpush($this->queueKey, serialize($jobPayload));
    }

    public function releaseJob(JobInterface $job)
    {
        // Returning the job back to the main queue from the processing queue
        $this->predis->rpoplpush($job->getProcessingKey(), $this->queueKey);
        $this->deleteLockedJob($job);
    }

    public function keepAlive(JobInterface $job)
    {
        // This command keeps Predis connection alive during long-running processing,
        // called in HandleQueueCommand.php
        $this->predis->ping();
    }
}