<?php

$CONFIG = [];

include_once "vendor/autoload.php";
include_once "config.php";

$localPath = __DIR__.'/local.php';
if (file_exists($localPath)) {
    require_once $localPath;
}


foreach ($CONFIG as $key => $value) {
    if (!is_scalar($value)) {
        continue;
    }
    
    define($key, $value);
}

spl_autoload_register(function ($className) {
    $className = basename(str_replace("\\", "/", $className));
    include __DIR__."/adapters/".$className.'.php';
});
