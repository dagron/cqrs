<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Command\Queue\Subscribe;

use GpsLab\Component\Command\Command;
use GpsLab\Component\Command\Queue\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use Superbalist\PubSub\Redis\RedisPubSubAdapter;

class PredisSubscribeCommandQueue implements SubscribeCommandQueue
{
    /**
     * @var RedisPubSubAdapter
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $queue_name = '';

    /**
     * @param RedisPubSubAdapter $client
     * @param Serializer         $serializer
     * @param LoggerInterface    $logger
     * @param string             $queue_name
     */
    public function __construct(
        RedisPubSubAdapter $client,
        Serializer $serializer,
        LoggerInterface $logger,
        $queue_name
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->queue_name = $queue_name;
    }

    /**
     * Publish command to queue.
     *
     * @param Command $command
     *
     * @return bool
     */
    public function publish(Command $command)
    {
        $massage = $this->serializer->serialize($command);
        $this->client->publish($this->queue_name, $massage);

        return true;
    }

    /**
     * Subscribe on command queue.
     *
     * @param callable $handler
     */
    public function subscribe(callable $handler)
    {
        $this->client->subscribe($this->queue_name, function ($message) use ($handler) {
            try {
                $command = $this->serializer->deserialize($message);
            } catch (\Exception $e) { // catch only deserialize exception
                // it's a critical error
                // it is necessary to react quickly to it
                $this->logger->critical(
                    'Failed denormalize a command in the Redis queue',
                    [$message, $e->getMessage()]
                );

                // try denormalize in later
                $this->client->publish($this->queue_name, $message);
            }

            if (isset($command)) {
                call_user_func($handler, $command);
            }
        });
    }
}
