<?php

namespace Eventio\BBQ\Tests\Functional;

use Eventio\BBQ;
use Eventio\BBQ\Job\Payload\JobPayloadInterface;
use Eventio\BBQ\Queue\DirectoryQueue;

/**
 * @author Ville Mattila <ville@eventio.fi>
 */
class CustomPayloadClassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return BBQ
     */
    private function createDirectoryBBQ()
    {
        $bbq = new BBQ();
        $dirQueue = new DirectoryQueue('email_jobs', '/tmp/bbq/email_jobs');
        $bbq->registerQueue($dirQueue);

        return $bbq;
    }

    public function testEmailPayload()
    {
        $bbq = $this->createDirectoryBBQ();
        $bbq->pushJob('email_jobs', new EmailPayload('recipient@example.com', 'Test subject', 'Test body'));

        unset($bbq);

        $bbq = $this->createDirectoryBBQ();
        $job = $bbq->fetchJob('email_jobs');
        $this->assertInstanceOf(__NAMESPACE__ . '\\EmailPayload', $job->getPayload());

        $payload = $job->getPayload();
        $this->assertEquals('recipient@example.com', $payload->recipient);

        $bbq->finalizeJob($job);

        rmdir('/tmp/bbq/email_jobs');
    }

}

class EmailPayload implements JobPayloadInterface
{

    public function __construct($recipient, $subject, $body)
    {
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->body = $body;
    }

    public $recipient;
    public $subject;
    public $body;

    public function serialize()
    {
        return json_encode(array(
            'r' => $this->recipient,
            's' => $this->subject,
            'b' => $this->body
        ));
    }

    public function unserialize($serialized)
    {
        $json = json_decode($serialized);
        $this->recipient = $json->r;
        $this->subject = $json->s;
        $this->body = $json->b;
    }

}