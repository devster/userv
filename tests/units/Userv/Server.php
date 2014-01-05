<?php

namespace Userv\Tests\Units;

use Userv\Server as Serv;
use mageekguy\atoum;

class Server extends atoum\test
{
    public function beforeTestMethod()
    {
    }

    public function testAddressPort()
    {
        $serv = new Serv('127.0.0.1', '99');

        $class = new \ReflectionClass($serv);
        $method = $class->getMethod('initialize');
        $method->setAccessible(true);

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
        ;
    }
}
