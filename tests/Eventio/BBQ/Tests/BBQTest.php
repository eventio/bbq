<?php

namespace Eventio\BBQ\Tests;

use Eventio\BBQ;
use Eventio\BBQ\Queue\QueueInterface;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class BBQTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BBQ
     */
    protected $bbq;

    public function setUp()
    {
        $this->bbq = new BBQ();
    }

    public function testRegisterQueue()
    {
        $this->assertEquals(0, count($this->bbq->getQueues()));

        $queueMock = $this->getMock(__NAMESPACE__ . '\TestQueue');
        $queueMock->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('test_id'));

        $this->bbq->registerQueue($queueMock);
        $queues = $this->bbq->getQueues();

        $this->assertEquals(1, count($queues));
    }

    public function testGetQueue()
    {
        $queueMock = $this->getMock(__NAMESPACE__ . '\TestQueue');
        $queueMock->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('test_id'));

        $this->bbq->registerQueue($queueMock);

        $returnedQueue = $this->bbq->getQueue('test_id');

        $this->assertSame($returnedQueue, $queueMock);
    }

}

abstract class TestQueue implements QueueInterface
{
    
}