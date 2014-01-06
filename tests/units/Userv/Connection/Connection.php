<?php

namespace Userv\Connection\Tests\Units;

use Userv\Server;
use Userv\Connection\Connection as Conn;
use mageekguy\atoum;

class Connection extends atoum\test
{
    protected $tmpfile;

    public function afterTestMethod()
    {
        if ($this->tmpfile) {
            unlink($this->tmpfile);
        }
    }

    protected function getConnection()
    {
        if ($this->tmpfile) {
            unlink($this->tmpfile);
        }

        $this->tmpfile = tempnam(sys_get_temp_dir(), 'userv');
        $conn = new Conn;
        $conn->setSocketConnection(fopen($this->tmpfile, 'w+'));
        $conn->setServer(new \mock\Userv\Server);
        return $conn;
    }

    public function testConstruct()
    {
        $conn = $this->getConnection();
        $this
            ->exception(function() use ($conn) {
                $conn->setSocketConnection('test');
            })
                ->isInstanceOf('\InvalidArgumentException')
        ;

        $this
            ->object($conn->server)
                ->isInstanceOf('\Userv\Server')
        ;
    }

    public function testWrite()
    {
        $conn = $this->getConnection();

        $this
            ->boolean(fgets($conn->connection))
                ->isFalse()
            ->if($conn->write('hello'))
            ->and(fseek($conn->connection, 0))
            ->string(fgets($conn->connection))
                ->isEqualTo('hello')
        ;
    }
}
