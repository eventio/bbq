BBQ - Message Queue Abstraction Library for PHP
===============================================

BBQ is a message queue abstraction library for PHP (5.3+). The library supports
different queue types that you can mix as required by your application.

As the actual queue services are abstracted, you can use different queue types
in different environments.

Installation
------------

Via [composer.json](http://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup)

    "require": {
        "eventio/bbq": "dev-master"
    }

For Symfony2 projects, you can use [EventioBBQBundle](https://github.com/eventio/bbq-bundle)

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

### RedisQueue ([Redis](http://redis.io/) server)

    $redis = new \Predis\Client();
    $queueListKey = 'queue_key';
    $queue = new RedisQueue('queue_id', $redis, $queueListKey);

RedisQueue uses the [Predis PHP Library](https://github.com/nrk/predis) to access
the configured Redis servers. The actual queue is implemented by a Redis list.

* `pushJob()` adds the job payload to the list using `[LPUSH](http://redis.io/commands/lpush)`
* `fetchJob()` fetches the job using `[BRPOPLPUSH](http://redis.io/commands/brpoplpush)` or `[RPOPLPUSH](http://redis.io/commands/rpoplpush)`
* `finalizeJob()` deletes the job using `[LREM](http://redis.io/commands/lrem)` from the processing queue
* `releaseJob()` moves the job back to the list queue using `[RPOPLPUSH](http://redis.io/commands/lrem)`

Non-deleted but fetched jobs are returned to the queue when the script ends.

RedisQueue uses a concept of processing queue to ensure the queue reliability also in case of the client
failures. Processing queue lives in a special key only between `fetchJob()` and `finalizeJob()` (or `releaseJob()`) calls.
The processing queue key name is automatically constructed in `fetchJob()` call and follows by default the pattern
`<queue_name>:<host_name>:<pid>(random unique string)`. [Read more about reliable queue pattern.](http://redis.io/commands/rpoplpush)

**Queue Configuration**

You can further customize the queue configuration by passing fourth argument to the queue constructor.

    $queue = new RedisQueue('queue_id', $redis, $queueListKey, $configuration);

`$configuration` should be an associative array. The default configuration (and possible variables) are following.

    $configuration = array(
        'processing_queue_key_prefix' => '%q:%h:%p',
        'allow_infinite_blocking' => false,
        'skip_shutdown_release' => false,
    );

`processing_queue_key_prefix`:  The prefix pattern for the processing queue key.
There are a few placeholders that are replaced with actual values:
`%q` main queue name, `%h` hostname and `%p` PHP process ID.

`allow_infinite_blocking`: By default, if you do not pass any timeout
(or `NULL` or `0` or `false`) for `fetchJob()`, RedisQueue will do
non-blocking `[RPOPLPUSH](http://redis.io/commands/rpoplpush)`
call instead of blocking `[BRPOPLPUSH](http://redis.io/commands/brpoplpush)`. If the queue
contains no jobs, the function is returned immediately. If you set `allow_infinite_blocking` to `true` and
pass no timeout to `fetchJob()`, the queue forces to use `[BRPOPLPUSH](http://redis.io/commands/brpoplpush)` even
with no timeout (=infinite blocking). Use with care.

`ship_shutdown_release`: By default, the queue registers a call that releases
possibly unreleased and unfinished but fetched jobs back to the queue. Set to `true` to
disable the functionality.

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