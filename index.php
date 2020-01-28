<?php

include_once "common.php";

$factory = Bot\Adapters\BotFactory::factory("Telegram");

$factory->init();
