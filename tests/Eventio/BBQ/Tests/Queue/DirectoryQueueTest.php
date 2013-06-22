<?php

namespace Eventio\BBQ\Tests\Queue;

use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\DirectoryQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class DirectoryQueueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DirectoryQueue
     */
    private $queue;
    private $dir;

    public function setUp()
    {
        $this->dir = '/tmp/bbq_dir_queue_' . time();
        $this->queue = new DirectoryQueue('directory_queue_' . time(), $this->dir, array('skip_shutdown_release' => true));
    }

    public function testPushJob()
    {
        $job = $this->queue->pushJob(new StringPayload('Test file'));
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\DirectoryQueueJob', $job);

        $filePath = $job->getJobFilePath();
        $this->assertFileExists($filePath);
    }

    public function testFetchJob()
    {
        $this->queue->pushJob(new StringPayload('Test payload'));
        $fetchedJob = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\DirectoryQueueJob', $fetchedJob);

        $this->assertEquals('Test payload', $fetchedJob->getPayload());
    }

    public function testFileExistence()
    {
        $originalJob = $this->queue->pushJob(new StringPayload('Test payload'));
        $originalFilePath = $originalJob->getJobFilePath();

        $this->assertEquals(1, $this->getDirectoryFileCount());
        $this->assertFileExists($originalFilePath);

        $fetchedJob = $this->queue->fetchJob();
        $filePath = $fetchedJob->getJobFilePath();

        $this->assertFileNotExists($originalFilePath);
        $this->assertFileExists($filePath);
        $this->assertEquals(1, $this->getDirectoryFileCount());

        $this->queue->finalizeJob($fetchedJob);

        $this->assertEquals(0, $this->getDirectoryFileCount());
    }

    public function testReleaseUnfinishedJobs()
    {
        $this->queue->pushJob(new StringPayload('Test payload 1'));
        $this->queue->pushJob(new StringPayload('Test payload 2'));

        $fetchedJob1 = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\DirectoryQueueJob', $fetchedJob1);

        $fetchedJob2 = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\DirectoryQueueJob', $fetchedJob2);

        // Nr 1 is finished
        $this->queue->finalizeJob($fetchedJob1);

        // No more returned
        $this->assertNull($this->queue->fetchJob());

        // Releasing 
        $this->queue->releaseUnfinishedJobs();

        $fetchedJob3 = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\DirectoryQueueJob', $fetchedJob3);
    }

    private function getDirectoryFileCount()
    {
        $fileCount = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $fileCount++;
        }

        return $fileCount;
    }

    public function tearDown()
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        rmdir($this->dir);
    }

}