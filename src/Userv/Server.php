<?php

namespace Userv;

class Server
{
    public $socket;

    protected $isTelnet = false;
    protected $address;
    protected $port;
    protected $url;
    protected $flags;
    protected $context;
    protected $handler;

    public function __construct($address = null, $port = null)
    {
        $this
            ->setAddress($address)
            ->setPort($port)
        ;
    }

    public function setTelnet($bool)
    {
        $this->isTelnet = $bool;

        return $this;
    }

    public function isTelnet()
    {
        return $this->isTelnet;
    }

    /**
     * Configure the socket address
     * Might be a IPv4, IPv6 (must be wrap with brackets []), host
     *
     * @param string $address
     * @return  Server the current instance
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Configure the socket port
     *
     * @param integer $port
     * @return  Server the current instance
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Set the local_socket manually, useful in case of non classic tcp server
     *
     * @see http://php.net/manual/en/function.stream-socket-server.php
     * @param string $url
     * @return  Server the current instance
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the socket local_socket
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the socket flags, in case of non classic tcp server
     *
     * @see http://php.net/manual/en/function.stream-socket-server.php
     * @param integer $flags
     * @return  Server the current instance
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get the socket flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set the socket context
     *
     * @see http://php.net/manual/en/function.stream-socket-server.php
     * @param resource $context
     * @return  Server the current instance
     */
    public function setContext($context)
    {
        if (! is_resource($context) || 'stream-context' != get_resource_type($context)) {
            throw new \InvalidArgumentException('Context must be a resource of `stream-context` type');
        }

        $this->context = $context;

        return $this;
    }

    /**
     * Get the socket context
     *
     * @return resource
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the handler
     * This callable will be executed once per client
     * You can set this handler or extend the handle method
     *
     * @see  Server::handle
     * @param callable|Closure $handler
     * @return  Server the current instance
     */
    public function setHandler($handler)
    {
        if (! is_callable($handler)) {
            throw new \InvalidArgumentException('A handler must be a callable or a closure');
        }

        $this->handler = $handler;

        return $this;
    }

    /**
     * Extend this method to configure the server
     */
    public function configure()
    {
    }

    /**
     * Extend this method to handle client connections
     *
     * @see  Server::setHandler
     * @param  Connection $connection
     */
    public function handle(Connection $connection)
    {
        throw new \LogicException('A handler is required. Extend the method `handle` or use setHandler');
    }

    /**
     * Internal configuration check
     */
    protected function initialize()
    {
        $this->configure();

        if (is_null($this->handler)) {
            $this->handler = array($this, 'handle');
        }

        if (is_null($this->flags)) {
            $this->flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        }

        if (is_null($this->context)) {
            $this->context = stream_context_create();
        }

        if (is_null($this->url)) {
            if (is_null($this->address) || is_null($this->port)) {
                throw new \InvalidArgumentException('Both address and port are required if you don\'t set manually the url');
            }

            $this->url = sprintf('tcp://%s:%d', $this->address, $this->port);
        }

        $this->setAddress(null)->setPort(null);
    }

    /**
     * Runs indefinitely the socket server
     *
     * @throws \InvalidArgumentException If there is a problem in server configuration
     * @throws \RuntimeException If the socket creation fails or if the fork fails
     */
    public function run()
    {
        $this->initialize();

        $this->socket = @stream_socket_server($this->url, $errno, $errstr, $this->flags, $this->context);

        if (! $this->socket) {
            throw new \RuntimeException(sprintf(
                'Socket [%s] error: [%d] %s',
                $this->url,
                $errno,
                $errstr
            ));
        }

        while ($conn = stream_socket_accept($this->socket, -1)) {
            $connection = new Connection($conn, $this);

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
