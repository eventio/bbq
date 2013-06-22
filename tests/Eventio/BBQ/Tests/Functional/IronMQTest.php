<?php

namespace Eventio\BBQ\Tests\Functional;

use Eventio\BBQ;
use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\IronMQQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class IronMQTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        if (!getenv('IRON_MQ_TOKEN') || !getenv('IRON_MQ_PROJECT_ID')) {
            $this->markTestSkipped(
                    'Environment variables IRON_MQ_TOKEN and/or IRON_MQ_PROJECT_ID missing'
            );
        } elseif (!class_exists('\\IronMQ')) {
            $this->markTestSkipped(
                    'IronMQ Library is not installed'
            );
        }
    }

    public function createBBQ()
    {
        $bbq = new BBQ();

        $ironmq = new \IronMQ(array(
            'token' => getenv('IRON_MQ_TOKEN'),
            'project_id' => getenv('IRON_MQ_PROJECT_ID'),
        ));

        $bbq->registerQueue(new IronMQQueue('ironmq_test1', $ironmq, 'test_1'));

        return $bbq;
    }

    public function testJob()
    {

        $bbq = $this->createBBQ();
        $bbq->pushJob('ironmq_test1', new StringPayload('Test 1'));

        unset($bbq);

        $bbq = $this->createBBQ();
        $job = $bbq->fetchJob('ironmq_test1');

        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\IronMQQueueJob', $job);

        $payload = $job->getPayload();
        $this->assertEquals('Test 1', (string) $payload);

        $bbq->finalizeJob($job);
    }

}