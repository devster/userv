<?php

namespace Userv;

class Server
{
    protected $address;
    protected $port;
    protected $url;
    protected $handler;

    public function __construct($address = null, $port = null)
    {
        $this
            ->setAddress($address)
            ->setPort($port)
        ;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function setHandler($handler)
    {
        if (! is_callable($handler) || ! $handler instanceof \Closure) {
            throw new \InvalidArgumentException('A handler must be a callable or a closure');
        }

        $this->handler = $handler;

        return $this;
    }

    protected function initialize()
    {
        if (is_null($this->handler)) {
            throw new \LogicException('An handler must be set before running the server');
        }

        if (is_null($this->url)) {
            if (is_null($this->address) || is_null($this->port)) {
                throw new \InvalidArgumentException('Both address and port are required if you don\'t set manually the url');
            }

            $this->url = sprintf('tcp://%s:%d', $this->address, $this->port);
        }
    }

    public function run()
    {
        $this->initialize();

        $socket = @stream_socket_server($this->url, $errno, $errstr);

        if (! $socket) {
            throw new \RuntimeException(sprintf(
                'Socket [%s] error: [%d] %s',
                $this->url,
                $errno,
                $errstr
            ));
        }

        while ($conn = stream_socket_accept($socket, -1)) {
            $connection = new Connection($conn);

            if ($this->fork()) {
                $connection->close();
                continue;
            }

            call_user_func($this->handler, $connection);
            $connection->close();
        }
    }

    protected function fork()
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new \RuntimeException('Unable to fork');
        } else if ($pid) {
            // parent process
            return true;
        }

        return false;
    }
}
