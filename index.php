<?php

include_once "common.php";
use Bot\Database\PdoObjectAdapter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$factory = Bot\Adapters\BotFactory::factory("Telegram");

$db = new PdoObjectAdapter();

$factory->init($db);
