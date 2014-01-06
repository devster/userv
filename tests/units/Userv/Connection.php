<?php

namespace Userv\Tests\Units;

use Userv\Server;
use Userv\Connection as Conn;
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
        $this->tmpfile = tempnam(sys_get_temp_dir(), 'userv');
        return new Conn(fopen($this->tmpfile, 'w+'), new \mock\Userv\Server);
    }

    public function testConstruct()
    {
        $this
            ->exception(function() {
                new Conn('test', new Server);
            })
                ->isInstanceOf('\InvalidArgumentException')
        ;

        $this
            ->if($conn = $this->getConnection())
            ->object($conn->getServer())
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
