<?php
if (php_sapi_name() !== 'cli') {
    throw new Exception("Not found");
}

class CronSingletonWorker
{
    public function run()
    {
    
    }
    
    
}