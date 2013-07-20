<?php

namespace Eventio\BBQ\Queue;

use Eventio\BBQ\Job\DirectoryQueueJob;
use Eventio\BBQ\Job\JobInterface;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;
use Eventio\BBQ\Queue\AbstractQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class DirectoryQueue extends AbstractQueue
{

    protected $directory;

    public function __construct($id, $directory, array $config = array())
    {

        $this->directory = $directory;

        parent::__construct($id, $config);

        if (!array_key_exists('skip_shutdown_release', $config) || $config['skip_shutdown_release'] == false) {
            register_shutdown_function(array($this, 'releaseUnfinishedJobs'));
        }
    }

    public function mayHaveJob()
    {
        return true;
    }

    protected $jobPaths = array();

    public function fetchJob($timeout = null)
    {
        $iterator = $this->createIterator();
        foreach ($iterator as $file) {
            if (substr($file->getFileName(), -4) !== '.run') {
                $filePath = $file->getRealPath();
                $runFilePath = $filePath . '.run';

                // Renaming to lock the file
                if (false === rename($filePath, $runFilePath)) {
                    throw new \RuntimeException('Renaming from ' . $filePath . ' to ' . $runFilePath . ' failed.');
                }

                $jobPayload = unserialize(file_get_contents($runFilePath));
                $job = new DirectoryQueueJob($jobPayload, $runFilePath);
                $job->setQueue($this);

                $this->lockJob($job);
                return $job;
            }
        }

        return null;
    }

    public function finalizeJob(JobInterface $jobToFinalize)
    {
        $this->deleteLockedJob($jobToFinalize);
        return true;
    }

    public function deleteLockedJob(JobInterface $jobToDelete)
    {
        @unlink($jobToDelete->getJobFilePath());
        parent::deleteLockedJob($jobToDelete);
    }

    public function pushJob(JobPayloadInterface $payload)
    {
        $filename = $this->directory . '/' . time() . '-' . uniqid();
        file_put_contents($filename, serialize($payload));

        $jobObject = new DirectoryQueueJob($payload, $filename);
        return $jobObject;
    }

    /**
     * @return CallbackFilterIterator
     */
    protected function createIterator()
    {
        $directoryIterator = new \DirectoryIterator($this->directory);
        return new \CallbackFilterIterator($directoryIterator, function($fileInfo) {
                    return !$fileInfo->isDot();
                });
    }

    protected function init()
    {
        if (false === is_dir($this->directory)) {
            mkdir($this->directory, 0700, true);
        }
    }

    /**
     * @deprecated, use releaseLockedJobs() instead
     */
    public function releaseUnfinishedJobs()
    {
        $this->releaseLockedJobs();
    }
}