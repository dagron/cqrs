<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Tests\Command\Queue\Pull;

use GpsLab\Component\Command\Queue\Pull\PredisUniquePullCommandQueue;
use GpsLab\Component\Command\Queue\Serializer\Serializer;
use GpsLab\Component\Tests\Fixture\Command\CreateContact;
use GpsLab\Component\Tests\Fixture\Command\RenameContactCommand;
use Predis\Client;
use Psr\Log\LoggerInterface;

class PredisUniquePullCommandQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Client
     */
    private $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Serializer
     */
    private $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var PredisUniquePullCommandQueue
     */
    private $queue;

    /**
     * @var string
     */
    private $queue_name = 'unique_commands';

    protected function setUp()
    {
        $this->client = $this->getMock(Client::class);
        $this->serializer = $this->getMock(Serializer::class);
        $this->logger = $this->getMock(LoggerInterface::class);
        $this->queue = new PredisUniquePullCommandQueue(
            $this->client,
            $this->serializer,
            $this->logger,
            $this->queue_name
        );
    }

    public function testPushQueue()
    {
        $queue = [
            new RenameContactCommand(),
            new CreateContact(),
            new RenameContactCommand(), // duplicate
            new RenameContactCommand(), // duplicate
            new CreateContact(), // duplicate
            new RenameContactCommand(), // duplicate
        ];

        $i = 0;
        foreach ($queue as $command) {
            $value = $i.spl_object_hash($command);

            $this->serializer
                ->expects($this->at($i))
                ->method('serialize')
                ->with($command)
                ->will($this->returnValue($value))
            ;

            $this->client
                ->expects($this->at($i * 2))
                ->method('__call')
                ->with('lrem', [$this->queue_name, 0, $value])
                ->will($this->returnValue(1))
            ;
            $this->client
                ->expects($this->at((($i + 1) * 2) - 1))
                ->method('__call')
                ->with('rpush', [$this->queue_name, [$value]])
                ->will($this->returnValue(1))
            ;
            ++$i;
        }

        foreach ($queue as $command) {
            $this->assertTrue($this->queue->publish($command));
        }
    }

    public function testPopQueue()
    {
        $queue = [
            new RenameContactCommand(),
            new CreateContact(),
            new RenameContactCommand(), // duplicate
        ];

        $i = 0;
        foreach ($queue as $command) {
            $value = $i.spl_object_hash($command);

            $this->serializer
                ->expects($this->at($i))
                ->method('deserialize')
                ->with($value)
                ->will($this->returnValue($command))
            ;

            $this->client
                ->expects($this->at($i))
                ->method('__call')
                ->with('lpop', [$this->queue_name])
                ->will($this->returnValue($value))
            ;
            ++$i;
        }
        $this->client
            ->expects($this->at($i))
            ->method('__call')
            ->with('lpop', [$this->queue_name])
            ->will($this->returnValue(null))
        ;

        $expected = array_reverse($queue);
        $i = count($expected);
        while ($command = $this->queue->pull()) {
            $this->assertEquals($expected[--$i], $command);
        }

        $this->assertEquals(0, $i, 'Queue cleared');
        $this->assertNull($command, 'No commands in queue');
    }

    public function testFailedDeserialize()
    {
        $exception = new \Exception('foo');
        $command = new RenameContactCommand();
        $value = spl_object_hash($command);

        $this->client
            ->expects($this->at(0))
            ->method('__call')
            ->with('lpop', [$this->queue_name])
            ->will($this->returnValue($value))
        ;
        $this->client
            ->expects($this->at(1))
            ->method('__call')
            ->with('rpush', [$this->queue_name, [$value]])
            ->will($this->returnValue(1))
        ;

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($value)
            ->will($this->throwException($exception))
        ;

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with('Failed denormalize a command in the Redis queue', [$value, $exception->getMessage()])
            ->will($this->returnValue(1))
        ;

        $this->assertNull($this->queue->pull());
    }
}
