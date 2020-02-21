<?php

namespace Bot\Adapters;

abstract class AbstractBot
{
    public function getOption($key)
    {
        return getenv($key);
    } // end getOption
}