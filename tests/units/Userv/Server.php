<?php

namespace Userv\Tests\Units;

use Userv\Server as Serv;
use Userv\Connection;
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

    public function testContext()
    {
        $serv = new Serv('127.0.0.1', '99');
        $serv2 = clone $serv;
        $method = $this->getInitializeMethod();

        $this
            ->if($method->invoke($serv))
            ->boolean(is_resource($serv->getContext()))
                ->isTrue()
            ->string(get_resource_type($serv->getContext()))
                ->isEqualTo('stream-context')
            ->if($streamContext = stream_context_create())
            ->and($serv->setContext($streamContext))
            ->and($method->invoke($serv2))
            ->variable($serv->getContext())
                ->isEqualTo($streamContext)
                ->isNotEqualTo(stream_context_create())
            ->exception(function() use ($serv) {
                $serv->setContext('test');
            })
                ->isInstanceOf('\InvalidArgumentException')
                ->message
                    ->contains('Context must be a resource')
        ;
    }

    public function testHandler()
    {
        $serv = new Serv('127.0.0.1', '99');
        $serv2 = clone $serv;
        $method = $this->getInitializeMethod();

        $class = new \ReflectionClass($serv);
        $property = $class->getProperty('handler');
        $property->setAccessible(true);

        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $connMock = new \mock\Userv\Connection;

        $this
            ->exception(function() use ($serv, $connMock) {
                $serv->handle($connMock);
            })
                ->isInstanceOf('\LogicException')
            ->if($method->invoke($serv))
            ->variable($property->getValue($serv))
                ->isCallable()
                ->isEqualTo(array($serv, 'handle'))
            ->if($func = function(){})
            ->and($serv2->setHandler($func))
            ->and($method->invoke($serv2))
            ->variable($property->getValue($serv2))
                ->isEqualTo($func)
                ->isNotEqualTo(function(){})
            ->exception(function() use ($serv) {
                $serv->setHandler('test');
            })
                ->isInstanceOf('\InvalidArgumentException')
                ->message
                    ->contains('A handler must be a callable')
        ;
    }

    public function testConfigure()
    {
        $method = $this->getInitializeMethod();
        $mock = new \mock\Userv\Server('127.0.0.1', '99');

        $this
            ->if($method->invoke($mock))
            ->mock($mock)
                ->call('configure')
                    ->once()
        ;
    }

    public function testRun()
    {
        $serv = new \mock\Userv\Server('test', 55);

        $this
            ->exception(function() use ($serv) {
                $serv->run();
            })
                ->isInstanceOf('\RuntimeException')
                ->message
                    ->contains('Socket [tcp://test:55] error')
            ->mock($serv)
                ->call('configure')
                    ->once()
        ;
    }
}
