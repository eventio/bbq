<?php

namespace Eventio\BBQ\Tests\Queue;

use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\PheanstalkTubeQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class PheanstalkTubeQueueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PheanstalkTubeQueue
     */
    private $queue;

    public function setUp()
    {
        $pheanstalk = new \Pheanstalk_Pheanstalk('127.0.0.1');
        $this->queue = new PheanstalkTubeQueue('pheanstalk_tube1', $pheanstalk, 'tube1');
    }

    public function testJob()
    {
        $job = $this->queue->fetchJob(0);
        $this->assertNull($job);

        $this->queue->pushJob(new StringPayload('Test job'));
        $job = $this->queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\PheanstalkQueueJob', $job);
        $this->queue->finalizeJob($job);

        $job = $this->queue->fetchJob(0);
        $this->assertNull($job);
    }

    public function testFetchJobTimeout()
    {
        $job = $this->queue->fetchJob(0);
        $this->assertNull($job);

        $timeNow = time();
        $job = $this->queue->fetchJob(5);
        $timeAfter = time();

        $this->assertNull($job);
        $this->assertLessThanOrEqual(1, abs($timeAfter - $timeNow - 5));
    }

}