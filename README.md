Userv
=====

Micro PHP Socket server library

! work in progress!
For now only tested on ubuntu, PHP 5.4
This lib should be used only in CLI, because of the use of pcntl_fork

Quick usage
-----------

Create a php file and copy the code below.

```php
require '/userv/src/Userv/Server.php';
require '/userv/src/Userv/Connection.php';

use Userv\Server;

$serv = new Server('127.0.0.1', 23);
$serv
    ->setTelnet(true)
    ->setHandler(function($conn) {
        $conn->writeln('Hello, welcome on this telnet server, bouya!');
        $name = $conn->ask('What\'s your name? : ');
        $conn->writeln('Your name is '.$name);
    })
;

$serv->run();
```

Then run this file as CLI script: `sudo php myserver.php`.

Now open a new terminal and try `telnet localhost`.