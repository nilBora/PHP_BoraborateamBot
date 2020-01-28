<?php

	if (php_sapi_name() !== 'cli') {
	    throw new Exception("Not found");
	}
 
	include_once "./../common.php";
	
	$bot = new \TelegramBot\Api\Client(TELEGRAM_TOKEN);
	
	while(true) {
		
		$dataStore = json_decode(file_get_contents(STORE_PATH), true);
		
		if (!$dataStore) {
			sleep(1);
			continue;
		}
		foreach ($dataStore as $date => $data) {
			if ($date == date("Y-m-d")) {

				foreach ($data as $dateTime => $items) {
					$time = time();
					
					if (strtotime($dateTime) < time()) {
						foreach ($items as $idUser => $message) {

							$bot->sendMessage($idUser, $message);
							unset($dataStore[$date][$dateTime][$idUser]);
						}	
					}
					
				}
			}
		}

		file_put_contents(STORE_PATH, json_encode($dataStore));
		
		sleep(10);
	}
