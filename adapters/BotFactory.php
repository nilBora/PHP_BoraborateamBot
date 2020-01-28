<?php

namespace Bot\Adapters;

class BotFactory
{
    public static function factory($name)
    {
        $className = "\Bot\Adapters\\".$name;
        return new $className();
    } // end factory
    
}