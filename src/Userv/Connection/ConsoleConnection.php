<?php

namespace Userv\Connection;

class ConsoleConnection extends Connection
{
    /**
     * Ask a question to the client
     *
     * @param  string  $question  The question text
     * @param  string  $default   Default value if the client send an empty response
     * @param  boolean $canBeNull Whether the answer can be empty or not
     * @param  array   $choices   An array of response choices, if set, the client response must be in these choices
     * @return string The client response
     */
    public function ask($question, $default = null, $canBeNull = false, array $choices = array())
    {
        $this->write($question);
        $rep = trim($this->read());

        if ('' == $rep && $default) {
            return $default;
        }

        if ('' == $rep && ! $canBeNull) {
            $rep = $this->ask($question, $default, $canBeNull, $choices);
        }

        if (
            ! empty($choices)
            && ! in_array(strtolower($rep), array_map('strtolower', $choices))
        ) {
            $rep = $this->ask($question, $default, $canBeNull, $choices);
        }

        return $rep;
    }

    /**
     * Same as write but add an end line
     *
     * @param  string $msg
     */
    public function writeln($msg)
    {
        $this->write($msg."\n");
    }

    /**
     * Clean the current line and write
     *
     * @param  string $msg
     */
    public function overwrite($msg)
    {
        $this->write("\x0D");
        $this->write($msg);
    }
}
