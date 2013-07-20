<?php

namespace Eventio\BBQ\Tests\Functional;

use Eventio\BBQ;
use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\RedisQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    
    protected function setUp()
    {
        if (!class_exists('\\Predis\\Client')) {
            $this->markTestSkipped(
                    'Predis library is not installed'
            );
        }
    }

    public function createBBQRedisQueue()
    {
        $bbq = new BBQ();

        $predis = new \Predis\Client();
        $bbq->registerQueue(new RedisQueue('queue1', $predis, 'eventio:bbq:queue1'));

        return $bbq;
    }

    public function testJob()
    {

        $bbq = $this->createBBQRedisQueue();
        $bbq->pushJob('queue1', new StringPayload('Test 1'));

        unset($bbq);

        $bbq = $this->createBBQRedisQueue();
        $job = $bbq->fetchJob('queue1');

        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\RedisQueueJob', $job);

        $payload = $job->getPayload();
        $this->assertEquals('Test 1', (string) $payload);

        $bbq->finalizeJob($job);
    }

}