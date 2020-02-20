<?php

include_once "common.php";
use Bot\Database\PdoObjectAdapter;

$factory = Bot\Adapters\BotFactory::factory("Telegram");

$db = new PdoObjectAdapter();

$factory->init($db);
