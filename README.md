# Bot for help me
## Telegram Bot
### /msg
Отправляет сообщение по заданному времени

`/msg Текст сообщения | 21:00`

## Viber
### Reg hook
```php
require_once("vendor/autoload.php");
use Viber\Client;
$apiKey = 'XXX-XXX-XXX';
$webhookUrl = 'https://bot.develop-nil.com/vbot.php';
try {
    $client = new Client(['token' => $apiKey]);
    $result = $client->setWebhook($webhookUrl);
    echo "Success!\n";
} catch (Exception $e) {
    echo "Error: ". $e->getError() ."\n";
}
```
