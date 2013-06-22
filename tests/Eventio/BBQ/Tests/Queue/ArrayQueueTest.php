<?php

namespace Eventio\BBQ\Tests\Queue;

use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\ArrayQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class ArrayQueueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ArrayQueue
     */
    private $queue;

    public function setUp()
    {
        $this->queue = new ArrayQueue('array_queue_' . time());
    }

    public function testMayHaveJob()
    {
        $this->assertFalse($this->queue->mayHaveJob());

        $this->queue->pushJob(new StringPayload('Test job'));
        $this->assertTrue($this->queue->mayHaveJob());

        $job = $this->queue->fetchJob();

        $this->assertFalse($this->queue->mayHaveJob());
    }

    public function testFetchJob()
    {
        $this->queue->pushJob(new StringPayload('Test job 1'));
        $this->queue->pushJob(new StringPayload('Test job 2'));

        $job = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\Job', $job);

        $this->assertTrue($this->queue->mayHaveJob());

        $job2 = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\Job', $job2);

        $this->assertFalse($this->queue->mayHaveJob());
    }

    public function testEmptyQueueReturnsNull()
    {
        $job = $this->queue->fetchJob();
        $this->assertNull($job);
    }

    public function testFinalizeJob()
    {
        $this->queue->pushJob(new StringPayload('Test job'));

        $job = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\JobInterface', $job);
        $this->assertTrue($this->queue->hasLockedJobs());

        $this->queue->finalizeJob($job);

        $this->assertFalse($this->queue->hasLockedJobs());
    }

    public function testReleaseJobReturnsBackToQueue()
    {
        $this->queue->pushJob(new StringPayload('Test job'));

        $job = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\JobInterface', $job);

        $this->assertTrue($this->queue->hasLockedJobs());

        $this->queue->releaseJob($job);

        $this->assertFalse($this->queue->hasLockedJobs());

        $jobAgain = $this->queue->fetchJob();

        $this->assertSame($job->getPayload(), $jobAgain->getPayload());
    }

}