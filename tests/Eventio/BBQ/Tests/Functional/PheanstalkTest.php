<?php

namespace Eventio\BBQ\Tests\Functional;

use Eventio\BBQ;
use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\PheanstalkTubeQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class PheanstalkTest extends \PHPUnit_Framework_TestCase
{
    
    protected function setUp()
    {
        if (!class_exists('\\Pheanstalk_Pheanstalk')) {
            $this->markTestSkipped(
                    'Pheanstalk library is not installed.'
            );
        }
    }

    public function createPheanstalkQueue()
    {
        $bbq = new BBQ();

        $pheanstalk = new \Pheanstalk_Pheanstalk('127.0.0.1');
        $bbq->registerQueue(new PheanstalkTubeQueue('queue1', $pheanstalk, 'tube1'));

        return $bbq;
    }

    public function testJob()
    {

        $bbq = $this->createPheanstalkQueue();
        $bbq->pushJob('queue1', new StringPayload('Test 1'));

        unset($bbq);

        $bbq = $this->createPheanstalkQueue();
        $job = $bbq->fetchJob('queue1');

        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\PheanstalkQueueJob', $job);

        $payload = $job->getPayload();
        $this->assertEquals('Test 1', (string) $payload);

        $bbq->finalizeJob($job);
    }

}