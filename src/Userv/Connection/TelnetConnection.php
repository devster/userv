<?php

namespace Userv\Connection;

class TelnetConnection extends ConsoleConnection
{
    public function read()
    {
        static $firstUse = true;

        $rep = trim(fgets($this->connection));

        // TELNET HACK, the first time you get data from a telnet client
        // you receive bad data concatenated with the client data
        if ($firstUse) {
            $firstUse = false;
            $rep = substr($rep, strpos($rep, '#')+1);
        }

        return $rep;
    }
}
