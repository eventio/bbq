BBQ - Message Queue Abstraction Library for PHP
===============================================

BBQ is a message queue abstraction library for PHP (5.3+). The library supports
different queue types that you can mix as required by your application.

As the actual queue services are abstracted, you can use different queue types
in different environments.

Installation
------------

(WIP)

Basic Usage
-----------

You need at least one queue and register it with `BBQ()`. A BBQ Queue acts as an
abstraction layer to a message queue service.

    <?php

    $bbq   = new BBQ();
    $queue = new DirectoryQueue('tasks', '/var/lib/bbq/email_tasks');
    $bbq->registerQueue($queue);

After the queue is registered, you can consume the queue by
 - pushing messages; or
 - fetching messages

**Push messages**

    $bbq->pushJob('tasks', new StringPayload('New task payload'));

`BBQ::pushJob()` accepts to arguments:
 - id of the queue where to push the job
 - payload for the job

**Fetch messages**

    $job = $bbq->fetchJob('tasks');
    $payload = $job->getPayload();
    echo $payload; // Outputs "New task payload"

    $bbq->deleteJob($job);

`BBQ::fetchJob()` accepts two arguments:
 - id of the queue where from get a job (mandatory)
 - optionally the timeout (seconds) how long we should wait for the task from the queue

As you can see from the example above, you should explicitly delete the job with `deleteJob($job)` after
you have processed the job successfully. Otherwise, the job is most likely to be returned to the queue.
The actual behavior depends on the queue type.

Queues
------

A BBQ Queue combines the message queue service and the actual queue hosted by the service.
When you use BBQ in your application, you don't need to know which service actually hosts the queue.
You can easily use different queue types in different environments (dev, test, prod).

See Supported Queue Types below.

Each queue is registered with `BBQ()` with an id. The id is any string that identifies the queue
in your application.

Supported Queue Types
---------------------

### DirectoryQueue

    $queue = new DirectoryQueue('queue_id', '/tmp/queue');

DirectoryQueue persists jobs in files in the given directory.

Non-deleted but fetched jobs are returned to the queue as new jobs.

### PheanstalkTubeQueue (beanstalkd)

    $pheanstalk = new \Pheanstalk_Pheanstalk('127.0.0.1');
    $queue = new PheanstalkTubeQueue('queue_id', $pheanstalk, 'tube_name');

PheanstalkTubeQueue uses the [Pheanstalk library](https://github.com/pda/pheanstalk) to access
the configured beanstalkd server and one of it's tube. You need to pass an instance of `\Pheanstalk_Pheanstalk` and
the name of the tube to the constructor.

Non-deleted but fetched jobs are returned to the queue when the script ends.

### IronMQQueue

    $ironMQ = new \IronMQ(array(
        'token' => 'YOUR_IRONMQ_TOKEN',
        'project_id' => 'YOUR_IRONMQ_PROJECT_ID'
    ));
    $queue = new IronMQQueue('queue_id', $ironMQ, 'queue_name');

IronMQQueue accesses Iron.io's IronMQ service over HTTP(S) Interface.

Non-deleted but fetched jobs are returned to the queue if they are not deleted after timeout has passed. The timeout
is 60 seconds my default.

### Array Queue

    $queue = new ArrayQueue('queue_id');

The messages are stored in an array inside the queue object. Apparently this type
of queue is not persistent between PHP processes and is useful mainly in testing.

The whole queue is destroyed when the script ends.

Contribute
----------

As the library is in its very early stages, you are more than welcome to contribute the work
 - by fixing bugs
 - by writing new tests
 - by implementing new queue types
 - by giving ideas and comments on the code

License
-------

Copyright [Eventio Oy](https://github.com/eventio), [Ville Mattila](https://github.com/vmattila), 2013

Released under the [The MIT License](http://www.opensource.org/licenses/mit-license.php)