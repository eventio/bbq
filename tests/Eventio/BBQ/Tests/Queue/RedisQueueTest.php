<?php

namespace Eventio\BBQ\Tests\Queue;

use Eventio\BBQ\Job\Payload\StringPayload;
use Eventio\BBQ\Queue\RedisQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class RedisQueueTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        if (!class_exists('\\Predis\\Client')) {
            $this->markTestSkipped(
                    'Predis library is not installed'
            );
        }
    }

    /**
     * @return \Eventio\BBQ\Queue\RedisQueue
     */
    private function createRealQueue()
    {
        $predis = new \Predis\Client();
        return $this->createQueueOnPredis($predis);
    }

    /**
     * @return \Eventio\BBQ\Queue\RedisQueue
     */
    private function createQueueOnPredis($predis, $queueName = 'queue1')
    {
        $predis->del($queueName);
        return new RedisQueue('redis_queue1', $predis, $queueName);
    }

    public function testJob()
    {
        $queue = $this->createRealQueue();

        $job = $queue->fetchJob();
        $this->assertNull($job);

        $queue->pushJob(new StringPayload('RedisQueueTest::testJob'));
        $job = $queue->fetchJob();
        $this->assertInstanceOf('\\Eventio\\BBQ\\Job\\RedisQueueJob', $job);
        $this->assertTrue($queue->hasLockedJobs());
        
        $queue->finalizeJob($job);
        $this->assertFalse($queue->hasLockedJobs());
        
        $job = $queue->fetchJob();
        $this->assertNull($job);
    }

    public function testFetchJobTimeout()
    {
        $queue = $this->createRealQueue();

        $job = $queue->fetchJob();
        $this->assertNull($job);

        $timeNow = time();
        $job = $queue->fetchJob(5);
        $timeAfter = time();

        $this->assertNull($job);
        $this->assertLessThanOrEqual(1, abs($timeAfter - $timeNow - 5));
    }
    
    public function testFetchJobCall() {
        
        $predisStub = $this->getMockBuilder('\\Predis\\Client')
                     ->setMethods(array('rpoplpush'))
                     ->getMock();
        
        $queue = $this->createQueueOnPredis($predisStub);
        $queueName = 'queue1';
        
        $predisStub->expects($this->once())
                ->method('rpoplpush')
                ->with($this->equalTo($queueName), $this->anything());
        
        $job = $queue->fetchJob();
        $this->assertNull($job);
    }
    
    public function testFinalizeJobCall() {
        
        $predisStub = $this->getMockBuilder('\\Predis\\Client')
                     ->setMethods(array('lrem'))
                     //->enableProxyingToOriginalMethods() // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/132
                     ->getMock();
        
        $queue = $this->createQueueOnPredis($predisStub);
        $queueName = 'queue1';

        $queue->pushJob(new StringPayload('RedisQueueTest::testFinalizeJobCall'));        
        $job = $queue->fetchJob();
        $this->assertTrue($queue->hasLockedJobs());
        
        $predisStub->expects($this->once())
                ->method('lrem')
                ->with($this->equalTo($job->getProcessingKey()), $this->equalTo(0), $this->anything());
        
        $queue->finalizeJob($job);
        $this->assertFalse($queue->hasLockedJobs());
    }
    
    public function testFetchJobCallWithTimeout() {
        
        $predisStub = $this->getMockBuilder('\\Predis\\Client')
                     ->setMethods(array('brpoplpush'))
                     ->getMock();
        
        $queue = $this->createQueueOnPredis($predisStub);
        $queueName = 'queue1';
        $predisStub->expects($this->once())
                ->method('brpoplpush')
                ->with($this->equalTo($queueName), $this->anything());
        
        $job = $queue->fetchJob(1);
        $this->assertNull($job);
    }

}