<?php

namespace Userv\Tests\Units;

use Userv\Server as Serv;
use mageekguy\atoum;

class Server extends atoum\test
{
    public function beforeTestMethod()
    {
    }

    protected function getInitializeMethod()
    {
        $class = new \ReflectionClass(new Serv);
        $method = $class->getMethod('initialize');
        $method->setAccessible(true);

        return $method;
    }

    public function testAddressPort()
    {
        $serv = new Serv('127.0.0.1', '99');
        $method = $this->getInitializeMethod();

        $this
            ->if($method->invoke($serv))
            ->string($serv->getUrl())
                ->isEqualTo('tcp://127.0.0.1:99')
            ->if($serv->setAddress('10.10.10.10')->setPort(42))
            ->and($serv->setUrl(null))
            ->and($method->invoke($serv))
            ->string($serv->getUrl())
                ->isEqualTo('tcp://10.10.10.10:42')
            ->if($serv->setUrl('udp://8.8.8.8:10'))
            ->and($method->invoke($serv))
            ->string($serv->getUrl())
                ->isEqualTo('udp://8.8.8.8:10')
            ->if($serv = new Serv)
            ->exception(function() use ($method, $serv) {
                $method->invoke($serv);
            })
                ->isInstanceOf('\InvalidArgumentException')
                ->message
                    ->contains('Both address and port are required')
        ;
    }

    public function testFlags()
    {
        $serv = new Serv('127.0.0.1', '99');
        $method = $this->getInitializeMethod();

        $this
            ->if($method->invoke($serv))
            ->integer($serv->getFlags())
                ->isEqualTo(STREAM_SERVER_BIND | STREAM_SERVER_LISTEN)
            ->if($serv->setFlags(STREAM_SERVER_BIND))
            ->and($method->invoke($serv))
            ->integer($serv->getFlags())
                ->isEqualTo(STREAM_SERVER_BIND)
        ;
    }
}
