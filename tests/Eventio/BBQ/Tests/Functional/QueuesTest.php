<?php

namespace Eventio\BBQ\Tests\Functional;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class QueuesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return \Eventio\BBQ
     */
    private function createDirectoryBBQ()
    {
        $bbq = new \Eventio\BBQ();
        $dirQueue = new \Eventio\BBQ\Queue\DirectoryQueue('email_jobs', '/tmp/bbq/email_jobs');
        $bbq->registerQueue($dirQueue);

        return $bbq;
    }

    public function testQueues()
    {
        $bbq = $this->createDirectoryBBQ();
        $bbq->pushJob('email_jobs', new \Eventio\BBQ\Job\Payload\StringPayload('Email job 1'));

        unset($bbq);

        $bbq = $this->createDirectoryBBQ();
        $job = $bbq->fetchJob('email_jobs');
        $this->assertEquals('Email job 1', $job->getPayload());

        $bbq->finalizeJob($job);

        unset($bbq);

        $bbq = $this->createDirectoryBBQ();
        $job = $bbq->fetchJob('email_jobs');
        $this->assertNull($job);

        rmdir('/tmp/bbq/email_jobs');
    }

}

